<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alarm;
use App\Models\HydraulicReading;
use App\Models\ScadaReading;
use App\Models\TemperatureReading;
use App\Models\Turbine;
use App\Models\VibrationReading;
use App\Services\HistoricalAlarmAnalysisService;
use Illuminate\Http\Request;

class HistoryDataController extends Controller
{
    protected $historicalAlarmService;

    public function __construct(HistoricalAlarmAnalysisService $historicalAlarmService)
    {
        $this->historicalAlarmService = $historicalAlarmService;
    }
    public function loadAllHistoricalDataBetweenTwoPeriods(Request $request)
    {
        // Increase execution time for large data requests
        set_time_limit(300); // 5 minutes
        ini_set('memory_limit', '512M');

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();

        // Check if turbine exists
        if (!$turbine) {
            return response()->json([
                'error' => 'Turbine not found',
                'turbine_id' => $validated['turbine_id']
            ], 404);
        }

        $result = [];

        $turbineData = [
            'id' => $turbine->id,
            'turbine_id' => $turbine->turbine_id,
            'status' => $turbine->status,
            'scada' => $this->buildScadaDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
            'vibration' => $this->buildVibrationDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
            'hydraulic' => $this->buildHydraulicDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
            'temperature' => $this->buildTemperatureDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
            'alarms' => $this->buildAlarmsDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
        ];

        $result[] = $turbineData;

        return response()->json($result);
    }

    public function loadScadaDataBetweenTwoPeriods(Request $request) {

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $scadaData = $this->buildScadaDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
            'success' => true,
            'turbine' => $turbine,
            'scada_data' => $scadaData,
        ]);
    }

    public function loadHydraulicDataBetweenTwoPeriods(Request $request) {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $hydraulicData = $this->buildHydraulicDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
            'success' => true,
            'turbine' => $turbine,
            'hydraulic_data' => $hydraulicData,
        ]);
    }

    public function loadVibrationDataBetweenTwoPeriods(Request $request) {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $vibrationData = $this->buildVibrationDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
            'success' => true,
            'turbine' => $turbine,
            'vibration_data' => $vibrationData,
        ]);
    }

    public function loadTemperatureDataBetweenTwoPeriods(Request $request) {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $temperatureData = $this->buildTemperatureDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
            'success' => true,
            'turbine' => $turbine,
            'temperature_data' => $temperatureData,
        ]);
    }

    /**
     * NEW: Load alarms that occurred between two periods
     */
    public function loadAlarmsDataBetweenTwoPeriods(Request $request) {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $alarmsData = $this->buildAlarmsDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
            'success' => true,
            'turbine' => $turbine,
            'alarms_data' => $alarmsData,
        ]);
    }

    private function buildTemperatureDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $temperatureData = TemperatureReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->get();

        return [
            'temperature_data' => $temperatureData,
        ];
    }

    private function buildVibrationDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $vibrationData = VibrationReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->get();

        return [
            'vibration_data' => $vibrationData,
        ];
    }

    private function buildHydraulicDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $hydraulicData = HydraulicReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        return [
            'hydraulic_data' => $hydraulicData,
        ];
    }

    private function buildScadaDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $scadaData = ScadaReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        return [
            'scada_data' => $scadaData,
        ];
    }

    /**
     * Build alarms data between two periods
     * This uses RETROACTIVE ANALYSIS - it analyzes historical sensor data
     * and identifies what alarms WOULD HAVE occurred based on thresholds
     */
    private function buildAlarmsDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        // Option 1: Get actually stored alarms (if they exist)
        $storedAlarms = Alarm::where('turbine_id', $turbineId)
            ->whereBetween('detected_at', [$startDate, $endDate])
            ->orderBy('detected_at', 'desc')
            ->get()
            ->map(function($alarm) {
                return [
                    'id' => $alarm->id,
                    'alarm_code' => $alarm->alarm_code,
                    'alarm_type' => $alarm->alarm_type,
                    'component' => $alarm->component,
                    'severity' => $alarm->severity,
                    'status' => $alarm->status,
                    'message' => $alarm->message,
                    'data' => $alarm->data,
                    'detected_at' => $alarm->detected_at,
                    'resolved_at' => $alarm->resolved_at,
                    'acknowledged_at' => $alarm->acknowledged_at,
                    'resolution_notes' => $alarm->resolution_notes,
                    'alarm_details' => $alarm->getAlarmDetails(),
                    'duration_minutes' => $alarm->resolved_at
                        ? round($alarm->detected_at->diffInMinutes($alarm->resolved_at), 2)
                        : null,
                    'is_historical' => false, // Real alarm
                ];
            })->toArray();

        // Option 2: RETROACTIVE ANALYSIS - analyze historical sensor data
        $analysisResult = $this->historicalAlarmService->analyzeHistoricalPeriod(
            $turbineId,
            $startDate,
            $endDate
        );

        // Combine both sources
        $allAlarms = array_merge($storedAlarms, $analysisResult['alarms']);

        // Remove duplicates (prefer stored alarms over virtual ones)
        $uniqueAlarms = $this->deduplicateAlarms($allAlarms);

        // Sort by timestamp
        usort($uniqueAlarms, function($a, $b) {
            return strtotime($b['detected_at']) - strtotime($a['detected_at']);
        });

        // Calculate combined statistics
        $statistics = $this->calculateCombinedStatistics($uniqueAlarms);

        return [
            'alarms' => $uniqueAlarms,
            'statistics' => $statistics,
            'analysis_info' => [
                'stored_alarms_count' => count($storedAlarms),
                'retroactive_alarms_count' => count($analysisResult['alarms']),
                'total_unique_alarms' => count($uniqueAlarms),
                'period_start' => $startDate,
                'period_end' => $endDate,
            ]
        ];
    }

    /**
     * Remove duplicate alarms (prefer stored alarms over virtual ones)
     */
    private function deduplicateAlarms($alarms)
    {
        $unique = [];
        $seen = [];

        foreach ($alarms as $alarm) {
            // Create key: component + timestamp (rounded to nearest 5 minutes)
            $timestamp = strtotime($alarm['detected_at']);
            $roundedTime = floor($timestamp / 300) * 300; // Round to 5 minutes
            $key = $alarm['component'] . '_' . $roundedTime;

            if (!isset($seen[$key])) {
                $seen[$key] = $alarm;
            } else {
                // If we already have this alarm, prefer stored (non-historical) over virtual
                if (isset($alarm['is_historical']) && $alarm['is_historical'] === false) {
                    $seen[$key] = $alarm; // Replace virtual with stored
                }
            }
        }

        return array_values($seen);
    }

    /**
     * Calculate statistics from combined alarms
     */
    private function calculateCombinedStatistics($alarms)
    {
        $stats = [
            'total_alarms' => count($alarms),
            'by_severity' => [
                'warning' => 0,
                'critical' => 0,
                'failed' => 0,
            ],
            'by_type' => [
                'scada' => 0,
                'vibration' => 0,
                'temperature' => 0,
                'hydraulic' => 0,
            ],
            'by_status' => [
                'active' => 0,
                'acknowledged' => 0,
                'resolved' => 0,
            ],
            'most_common_components' => [],
            'average_resolution_time_minutes' => 0,
            'total_downtime_hours' => 0,
        ];

        $componentCounts = [];
        $totalDuration = 0;
        $resolvedCount = 0;
        $failedDuration = 0;

        foreach ($alarms as $alarm) {
            // Count by severity
            if (isset($stats['by_severity'][$alarm['severity']])) {
                $stats['by_severity'][$alarm['severity']]++;
            }

            // Count by type
            if (isset($stats['by_type'][$alarm['alarm_type']])) {
                $stats['by_type'][$alarm['alarm_type']]++;
            }

            // Count by status (if available)
            if (isset($alarm['status']) && isset($stats['by_status'][$alarm['status']])) {
                $stats['by_status'][$alarm['status']]++;
            }

            // Count components
            $component = $alarm['component'];
            if (!isset($componentCounts[$component])) {
                $componentCounts[$component] = 0;
            }
            $componentCounts[$component]++;

            // Calculate duration
            if (isset($alarm['duration_minutes']) && $alarm['duration_minutes'] !== null) {
                $totalDuration += $alarm['duration_minutes'];
                $resolvedCount++;

                // Count failed alarms as downtime
                if ($alarm['severity'] === 'failed') {
                    $failedDuration += $alarm['duration_minutes'];
                }
            }
        }

        // Sort components by frequency
        arsort($componentCounts);
        $stats['most_common_components'] = array_slice($componentCounts, 0, 5, true);

        // Calculate averages
        if ($resolvedCount > 0) {
            $stats['average_resolution_time_minutes'] = round($totalDuration / $resolvedCount, 2);
        }

        $stats['total_downtime_hours'] = round($failedDuration / 60, 2);

        return $stats;
    }
}
