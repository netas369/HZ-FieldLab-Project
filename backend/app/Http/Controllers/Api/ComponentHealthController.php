<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Turbine;
use App\Services\ComponentHealthService;
use Illuminate\Http\Request;

class ComponentHealthController extends Controller
{
    protected $healthService;

    public function __construct(ComponentHealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    /**
     * Get health data for all components of a turbine
     *
     * @param int $turbineId
     * @param int $daysBack (optional, default 365)
     *
     * Example: /api/turbines/1/component-health?days_back=90
     */
    public function getTurbineComponentHealth(Request $request, $turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);

        $daysBack = $request->input('days_back', 365);
        $daysBack = min(365, max(7, $daysBack)); // Between 7-365 days

        $healthData = $this->healthService->calculateAllComponentsHealth($turbineId, $daysBack);

        return response()->json($healthData);
    }

    /**
     * Get health for all turbines (summary view)
     *
     * Example: /api/turbines/component-health/summary
     */
    public function getAllTurbinesHealthSummary(Request $request)
    {
        $turbines = Turbine::all();
        $daysBack = $request->input('days_back', 365);
        $daysBack = min(365, max(7, $daysBack));

        $result = [];

        foreach ($turbines as $turbine) {
            $healthData = $this->healthService->calculateAllComponentsHealth($turbine->id, $daysBack);

            // Extract just the summary data
            $result[] = [
                'turbine_id' => $turbine->turbine_id,
                'turbine_status' => $turbine->status,
                'overall_health' => $healthData['overall_health'],
                'component_summary' => [
                    'main_bearing' => [
                        'health_score' => $healthData['components']['main_bearing']['health_score'],
                        'status' => $healthData['components']['main_bearing']['status'],
                        'deterioration_level' => $healthData['components']['main_bearing']['trend_analysis']['deterioration_level'] ?? null,
                    ],
                    'gearbox' => [
                        'health_score' => $healthData['components']['gearbox']['health_score'],
                        'status' => $healthData['components']['gearbox']['status'],
                        'deterioration_level' => $healthData['components']['gearbox']['trend_analysis']['deterioration_level'] ?? null,
                    ],
                    'generator' => [
                        'health_score' => $healthData['components']['generator']['health_score'],
                        'status' => $healthData['components']['generator']['status'],
                        'deterioration_level' => $healthData['components']['generator']['trend_analysis']['deterioration_level'] ?? null,
                    ],
                    'hydraulic_system' => [
                        'health_score' => $healthData['components']['hydraulic_system']['health_score'],
                        'status' => $healthData['components']['hydraulic_system']['status'],
                        'deterioration_level' => $healthData['components']['hydraulic_system']['trend_analysis']['deterioration_level'] ?? null,
                    ],
                ],
            ];
        }

        return response()->json([
            'period_days' => $daysBack,
            'calculation_timestamp' => now(),
            'turbines' => $result,
        ]);
    }

    /**
     * Get health for specific component across all turbines
     *
     * Example: /api/component-health/main_bearing
     */
    public function getComponentHealthAcrossTurbines(Request $request, $componentName)
    {
        $turbines = Turbine::all();
        $daysBack = $request->input('days_back', 365);
        $daysBack = min(365, max(7, $daysBack));

        $validComponents = [
            'main_bearing',
            'gearbox',
            'generator',
            'blade_1',
            'blade_2',
            'blade_3',
            'hydraulic_system',
            'tower'
        ];

        if (!in_array($componentName, $validComponents)) {
            return response()->json([
                'error' => 'Invalid component name',
                'valid_components' => $validComponents
            ], 400);
        }

        $result = [];

        foreach ($turbines as $turbine) {
            $healthData = $this->healthService->calculateAllComponentsHealth($turbine->id, $daysBack);

            $componentData = $healthData['components'][$componentName];

            $result[] = [
                'turbine_id' => $turbine->turbine_id,
                'turbine_status' => $turbine->status,
                'component' => $componentName,
                'health_score' => $componentData['health_score'],
                'status' => $componentData['status'],
                'deterioration_level' => $componentData['trend_analysis']['deterioration_level'] ?? null,
                'days_to_critical' => $componentData['trend_analysis']['days_to_critical'] ?? null,
            ];
        }

        // Sort by health score (worst first)
        usort($result, function($a, $b) {
            if ($a['health_score'] === null) return 1;
            if ($b['health_score'] === null) return -1;
            return $a['health_score'] <=> $b['health_score'];
        });

        return response()->json([
            'component' => $componentName,
            'period_days' => $daysBack,
            'calculation_timestamp' => now(),
            'turbines' => $result,
        ]);
    }

    /**
     * Get detailed health for a specific component of a turbine
     *
     * Example: /api/turbines/1/component-health/main_bearing
     */
    public function getSpecificComponentHealth(Request $request, $turbineId, $componentName)
    {
        $turbine = Turbine::findOrFail($turbineId);
        $daysBack = $request->input('days_back', 365);
        $daysBack = min(365, max(7, $daysBack));

        $validComponents = [
            'main_bearing',
            'gearbox',
            'generator',
            'blade_1',
            'blade_2',
            'blade_3',
            'hydraulic_system',
            'tower'
        ];

        if (!in_array($componentName, $validComponents)) {
            return response()->json([
                'error' => 'Invalid component name',
                'valid_components' => $validComponents
            ], 400);
        }

        $healthData = $this->healthService->calculateAllComponentsHealth($turbineId, $daysBack);
        $componentData = $healthData['components'][$componentName];

        return response()->json([
            'turbine_id' => $turbine->turbine_id,
            'turbine_status' => $turbine->status,
            'component_data' => $componentData,
            'calculation_timestamp' => $healthData['calculation_timestamp'],
            'period_days' => $daysBack,
        ]);
    }


    /**
     * Get deterioration trends for a turbine
     * Shows which components are deteriorating fastest
     *
     * Example: /api/turbines/1/deterioration-trends
     */
    public function getDeteriorationTrends(Request $request, $turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);
        $daysBack = $request->input('days_back', 365);
        $daysBack = min(365, max(7, $daysBack));

        $healthData = $this->healthService->calculateAllComponentsHealth($turbineId, $daysBack);

        $trends = [];

        foreach ($healthData['components'] as $componentName => $componentData) {
            if ($componentData['health_score'] !== null) {
                $trends[] = [
                    'component' => $componentName,
                    'health_score' => $componentData['health_score'],
                    'status' => $componentData['status'],
                    'deterioration_points' => $componentData['trend_analysis']['deterioration_points'] ?? null,
                    'deterioration_rate_per_year' => $componentData['trend_analysis']['deterioration_rate_per_year'] ?? null,
                    'deterioration_level' => $componentData['trend_analysis']['deterioration_level'] ?? null,
                    'days_to_critical' => $componentData['trend_analysis']['days_to_critical'] ?? null,
                ];
            }
        }

        // Sort by deterioration rate (fastest declining first)
        usort($trends, function($a, $b) {
            $rateA = $a['deterioration_rate_per_year'] ?? 0;
            $rateB = $b['deterioration_rate_per_year'] ?? 0;
            return $rateB <=> $rateA; // Descending
        });

        return response()->json([
            'turbine_id' => $turbine->turbine_id,
            'period_days' => $daysBack,
            'calculation_timestamp' => now(),
            'trends' => $trends,
            'fastest_deteriorating' => $trends[0] ?? null,
        ]);
    }
}
