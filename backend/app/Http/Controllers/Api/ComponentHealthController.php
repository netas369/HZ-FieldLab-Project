<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Turbine;
use App\Services\ComponentHealthService;
use Illuminate\Http\Request;

class ComponentHealthController extends Controller
{
    protected ComponentHealthService $healthService;

    public function __construct(ComponentHealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    /**
     * Get data availability analysis for a turbine
     * Helps understand what analysis is possible
     *
     * GET /api/v2/turbines/{turbineId}/data-availability
     */
    public function getDataAvailability($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);

        $availability = $this->healthService->analyzeDataAvailability($turbineId);

        return response()->json([
            'turbine_id' => $turbine->turbine_id,
            'data_availability' => $availability,
        ]);
    }

    /**
     * Get smart health analysis for all components
     * Auto-detects optimal analysis period based on available data
     *
     * GET /api/v2/turbines/{turbineId}/component-health
     * Optional: ?days_back=90 (will be capped at available data)
     */
    public function getTurbineComponentHealth(Request $request, $turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);

        // Allow override but let the service determine optimal if not specified
        $daysBack = $request->has('days_back')
            ? min(365, max(7, (int)$request->input('days_back')))
            : null;

        $healthData = $this->healthService->calculateAllComponentsHealth($turbineId, $daysBack);

        $healthData['turbine_identifier'] = $turbine->turbine_id;
        $healthData['turbine_status'] = $turbine->status;

        return response()->json($healthData);
    }

    /**
     * Get health summary for all turbines
     *
     * GET /api/v2/turbines/component-health/summary
     */
    public function getAllTurbinesHealthSummary(Request $request)
    {
        $turbines = Turbine::all();
        $daysBack = $request->has('days_back')
            ? min(365, max(7, (int)$request->input('days_back')))
            : null;

        $results = [];

        foreach ($turbines as $turbine) {
            $healthData = $this->healthService->calculateAllComponentsHealth($turbine->id, $daysBack);

            // Extract summary
            $componentSummary = [];
            foreach ($healthData['components'] as $name => $data) {
                $componentSummary[$name] = [
                    'health_score' => $data['health_score'],
                    'status' => $data['status'],
                    'deterioration_level' => $data['deterioration_analysis']['deterioration_level'] ?? null,
                    'days_to_critical' => $data['deterioration_analysis']['predictions']['days_to_critical'] ?? null,
                ];
            }

            $results[] = [
                'turbine_id' => $turbine->turbine_id,
                'turbine_status' => $turbine->status,
                'analysis_period_days' => $healthData['analysis_period']['days_analyzed'],
                'analysis_confidence' => $healthData['analysis_period']['confidence_level'],
                'overall_health' => $healthData['overall_health'],
                'component_summary' => $componentSummary,
            ];
        }

        // Sort by overall health (worst first)
        usort($results, function($a, $b) {
            return ($a['overall_health']['overall_health_score'] ?? 100) <=> ($b['overall_health']['overall_health_score'] ?? 100);
        });

        return response()->json([
            'calculation_timestamp' => now()->toDateTimeString(),
            'turbine_count' => count($results),
            'turbines' => $results,
        ]);
    }

    /**
     * Get specific component health with full trend analysis
     *
     * GET /api/v2/turbines/{turbineId}/component-health/{componentName}
     */
    public function getSpecificComponentHealth(Request $request, $turbineId, $componentName)
    {
        $turbine = Turbine::findOrFail($turbineId);

        $validComponents = [
            'main_bearing', 'gearbox', 'generator',
            'blade_1', 'blade_2', 'blade_3',
            'hydraulic_system', 'tower'
        ];

        if (!in_array($componentName, $validComponents)) {
            return response()->json([
                'error' => 'Invalid component name',
                'valid_components' => $validComponents
            ], 400);
        }

        $daysBack = $request->has('days_back')
            ? min(365, max(7, (int)$request->input('days_back')))
            : null;

        $healthData = $this->healthService->calculateAllComponentsHealth($turbineId, $daysBack);

        return response()->json([
            'turbine_id' => $turbine->turbine_id,
            'turbine_status' => $turbine->status,
            'analysis_period' => $healthData['analysis_period'],
            'component_data' => $healthData['components'][$componentName],
        ]);
    }

    /**
     * Get components that need attention (sorted by urgency)
     *
     * GET /api/v2/component-health/attention-required
     */
    public function getComponentsNeedingAttention(Request $request)
    {
        $turbines = Turbine::all();
        $daysBack = $request->has('days_back')
            ? min(365, max(7, (int)$request->input('days_back')))
            : null;

        $attentionRequired = [];

        foreach ($turbines as $turbine) {
            $healthData = $this->healthService->calculateAllComponentsHealth($turbine->id, $daysBack);

            foreach ($healthData['components'] as $componentName => $data) {
                if ($data['health_score'] === null) continue;

                $daysToCritical = $data['deterioration_analysis']['predictions']['days_to_critical'] ?? null;
                $deteriorationLevel = $data['deterioration_analysis']['deterioration_level'] ?? 'UNKNOWN';

                // Include if: health < 70 OR days to critical < 180
                $needsAttention = $data['health_score'] < 70 ||
                    ($daysToCritical !== null && $daysToCritical < 180);

                if ($needsAttention) {
                    $attentionRequired[] = [
                        'turbine_id' => $turbine->turbine_id,
                        'component' => $componentName,
                        'health_score' => $data['health_score'],
                        'status' => $data['status'],
                        'deterioration_level' => $deteriorationLevel,
                        'days_to_critical' => $daysToCritical,
                        'recommendation' => $data['deterioration_analysis']['recommendation'] ?? null,
                        'urgency_score' => $this->calculateUrgencyScore($data['health_score'], $daysToCritical),
                    ];
                }
            }
        }

        // Sort by urgency score (highest first)
        usort($attentionRequired, fn($a, $b) => $b['urgency_score'] <=> $a['urgency_score']);

        return response()->json([
            'calculation_timestamp' => now()->toDateTimeString(),
            'components_needing_attention' => count($attentionRequired),
            'components' => $attentionRequired,
        ]);
    }

    /**
     * Get deterioration trends across all turbines
     * Shows which components are deteriorating fastest
     *
     * GET /api/v2/deterioration-trends
     */
    public function getDeteriorationTrends(Request $request)
    {
        $turbines = Turbine::all();
        $daysBack = $request->has('days_back')
            ? min(365, max(7, (int)$request->input('days_back')))
            : null;

        $allTrends = [];

        foreach ($turbines as $turbine) {
            $healthData = $this->healthService->calculateAllComponentsHealth($turbine->id, $daysBack);

            foreach ($healthData['components'] as $componentName => $data) {
                if (!($data['deterioration_analysis']['can_analyze'] ?? false)) continue;

                // Get the worst sensor trend for this component
                $worstTrend = null;
                $sensorTrends = $data['sensor_trends'] ?? [];

                foreach ($sensorTrends as $sensorName => $trend) {
                    if (!($trend['has_sufficient_data'] ?? false)) continue;

                    $trendDirection = $trend['trend']['direction'] ?? 'STABLE';
                    $slopePerYear = abs($trend['trend']['slope_per_year'] ?? 0);

                    if ($worstTrend === null || $slopePerYear > ($worstTrend['slope_per_year'] ?? 0)) {
                        $worstTrend = [
                            'sensor' => $sensorName,
                            'direction' => $trendDirection,
                            'slope_per_year' => $slopePerYear,
                            'r_squared' => $trend['trend']['r_squared'] ?? 0,
                            'confidence' => $trend['trend']['confidence'] ?? 'UNKNOWN',
                        ];
                    }
                }

                $allTrends[] = [
                    'turbine_id' => $turbine->turbine_id,
                    'component' => $componentName,
                    'health_score' => $data['health_score'],
                    'deterioration_level' => $data['deterioration_analysis']['deterioration_level'] ?? null,
                    'days_to_critical' => $data['deterioration_analysis']['predictions']['days_to_critical'] ?? null,
                    'worst_trend' => $worstTrend,
                ];
            }
        }

        // Sort by urgency (days to critical, then health score)
        usort($allTrends, function($a, $b) {
            // Prioritize those with days_to_critical set
            if ($a['days_to_critical'] !== null && $b['days_to_critical'] === null) return -1;
            if ($a['days_to_critical'] === null && $b['days_to_critical'] !== null) return 1;
            if ($a['days_to_critical'] !== null && $b['days_to_critical'] !== null) {
                return $a['days_to_critical'] <=> $b['days_to_critical'];
            }
            // Then by health score
            return ($a['health_score'] ?? 100) <=> ($b['health_score'] ?? 100);
        });

        return response()->json([
            'calculation_timestamp' => now()->toDateTimeString(),
            'total_components_analyzed' => count($allTrends),
            'trends' => $allTrends,
        ]);
    }

    /**
     * Calculate urgency score for prioritization
     */
    private function calculateUrgencyScore(?float $healthScore, ?int $daysToCritical): float
    {
        $score = 0;

        // Health-based score (0-50)
        if ($healthScore !== null) {
            $score += max(0, 50 - ($healthScore / 2));
        }

        // Days to critical score (0-50)
        if ($daysToCritical !== null) {
            if ($daysToCritical <= 7) $score += 50;
            elseif ($daysToCritical <= 30) $score += 40;
            elseif ($daysToCritical <= 90) $score += 30;
            elseif ($daysToCritical <= 180) $score += 20;
            else $score += 10;
        }

        return $score;
    }
}
