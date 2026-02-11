<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HydraulicReading;
use App\Models\ScadaReading;
use App\Models\TemperatureReading;
use App\Models\Turbine;
use App\Models\VibrationReading;
use App\Services\AlarmService;
use App\Services\TurbineDataService;
use Illuminate\Http\Request;

class LiveDataController extends Controller
{
    protected $service;
    protected $alarmService;

    public function __construct(TurbineDataService $service, AlarmService $alarmService)
    {
        $this->service = $service;
        $this->alarmService = $alarmService;
    }

    /**
     * Get all turbines with all their data in one call
     */
    public function getAllTurbinesData()
    {
        $turbines = Turbine::all();
        $result = [];

        foreach ($turbines as $turbine) {
            // Check alarms once per turbine
            $this->alarmService->checkAndCreateAlarms($turbine->id);
            $this->alarmService->updateTurbineStatus($turbine->id);

            // Refresh turbine to get updated status
            $turbine->refresh();

            $turbineData = [
                'id' => $turbine->id,
                'turbine_id' => $turbine->turbine_id,
                'status' => $turbine->status,
                'created_at' => $turbine->created_at,
                'scada' => $this->buildScadaData($turbine->id),
                'hydraulic' => $this->buildHydraulicData($turbine->id),
                'vibration' => $this->buildVibrationData($turbine->id),
                'temperature' => $this->buildTemperatureData($turbine->id),
                'alarms' => $this->buildAlarmsData($turbine->id),
            ];

            $result[] = $turbineData;
        }

        return response()->json($result);
    }

    /**
     * Get analytics data with time range support
     */
    public function getAnalyticsData(Request $request)
    {
        // Check if custom date range is provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->input('start_date'));
            $endDate = \Carbon\Carbon::parse($request->input('end_date'))->endOfDay();
            $timeRange = 'custom';
        } else {
            $timeRange = $request->input('time_range', '7d');

            // Calculate date range based on time_range parameter
            $endDate = now();
            $startDate = match($timeRange) {
                '24h' => now()->subHours(24),
                '7d' => now()->subDays(7),
                '30d' => now()->subDays(30),
                '90d' => now()->subDays(90),
                default => now()->subDays(7),
            };
        }

        $turbines = Turbine::all();
        $result = [
            'time_range' => $timeRange,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'turbines' => [],
            'aggregated_metrics' => []
        ];

        foreach ($turbines as $turbine) {
            // Get current status
            $this->alarmService->checkAndCreateAlarms($turbine->id);
            $this->alarmService->updateTurbineStatus($turbine->id);
            $turbine->refresh();

            // Get latest readings for current state
            $latestScada = $this->buildScadaData($turbine->id);
            $latestHydraulic = $this->buildHydraulicData($turbine->id);
            $latestVibration = $this->buildVibrationData($turbine->id);
            $latestTemperature = $this->buildTemperatureData($turbine->id);

            // Get historical SCADA data for the time range
            $historicalScada = ScadaReading::where('turbine_id', $turbine->id)
                ->whereBetween('reading_timestamp', [$startDate, $endDate])
                ->get();

            // Calculate aggregated metrics
            $avgPower = $historicalScada->avg('power_kw') ?? 0;
            $maxPower = $historicalScada->max('power_kw') ?? 0;
            $minPower = $historicalScada->min('power_kw') ?? 0;
            $avgWindSpeed = $historicalScada->avg('wind_speed_ms') ?? 0;
            $maxWindSpeed = $historicalScada->max('wind_speed_ms') ?? 0;

            // Get alarms in time range
            $alarmsInRange = $this->alarmService->getAllAlarms($turbine->id)
                ->filter(function($alarm) use ($startDate, $endDate) {
                    $detectedAt = \Carbon\Carbon::parse($alarm->detected_at);
                    return $detectedAt->between($startDate, $endDate);
                })
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
                        'alarm_details' => $alarm->getAlarmDetails(),
                    ];
                })->values();

            $turbineData = [
                'id' => $turbine->id,
                'turbine_id' => $turbine->turbine_id,
                'status' => $turbine->status,
                'current_readings' => [
                    'scada' => $latestScada,
                    'hydraulic' => $latestHydraulic,
                    'vibration' => $latestVibration,
                    'temperature' => $latestTemperature,
                ],
                'aggregated_metrics' => [
                    'avg_power_kw' => round($avgPower, 2),
                    'max_power_kw' => round($maxPower, 2),
                    'min_power_kw' => round($minPower, 2),
                    'avg_wind_speed_ms' => round($avgWindSpeed, 2),
                    'max_wind_speed_ms' => round($maxWindSpeed, 2),
                    'data_points' => $historicalScada->count(),
                ],
                'alarms' => $alarmsInRange
            ];

            $result['turbines'][] = $turbineData;
        }

        // Calculate fleet-wide aggregated metrics
        $totalTurbines = count($result['turbines']);
        $runningTurbines = collect($result['turbines'])->where('status', 100)->count();
        $totalAlarms = collect($result['turbines'])->sum(fn($t) => count($t['alarms']));
        $avgFleetPower = collect($result['turbines'])->avg('aggregated_metrics.avg_power_kw') ?? 0;
        $totalFleetPower = collect($result['turbines'])->sum('aggregated_metrics.avg_power_kw') ?? 0;
        $avgFleetWindSpeed = collect($result['turbines'])->avg('aggregated_metrics.avg_wind_speed_ms') ?? 0;

        $result['aggregated_metrics'] = [
            'total_turbines' => $totalTurbines,
            'running_turbines' => $runningTurbines,
            'fleet_availability_percent' => $totalTurbines > 0 ? round(($runningTurbines / $totalTurbines) * 100, 2) : 0,
            'total_alarms' => $totalAlarms,
            'avg_fleet_power_kw' => round($avgFleetPower, 2),
            'total_fleet_power_kw' => round($totalFleetPower, 2),
            'avg_fleet_wind_speed_ms' => round($avgFleetWindSpeed, 2),
        ];

        return response()->json($result);
    }

    /**
     * API ENDPOINTS (keep for individual turbine requests)
     */

    public function getScadaData($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);

        // ✅ ONE CALL TO CHECK ALL SENSORS
        $this->alarmService->checkAndCreateAlarms($turbineId);
        $this->alarmService->updateTurbineStatus($turbineId);

        $scadaData = $this->buildScadaData($turbineId);

        if (!$scadaData) {
            return response()->json(['error' => 'No SCADA data found'], 404);
        }

        return response()->json($scadaData);
    }

    public function getHydraulicData($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);
        $hydraulicData = $this->buildHydraulicData($turbineId);

        if (!$hydraulicData) {
            return response()->json([
                'turbine_id' => $turbineId,
                'message' => 'No hydraulic readings found for this turbine'
            ], 404);
        }

        return response()->json($hydraulicData);
    }

    public function getVibrationsData($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);
        $vibrationData = $this->buildVibrationData($turbineId);

        if (!$vibrationData) {
            return response()->json([
                'turbine_id' => $turbineId,
                'message' => 'No vibration readings found'
            ], 404);
        }

        return response()->json($vibrationData);
    }

    public function getTemperatureData($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);
        $temperatureData = $this->buildTemperatureData($turbineId);

        if (!$temperatureData) {
            return response()->json([
                'error' => 'No temperature data found for this turbine'
            ], 404);
        }

        return response()->json($temperatureData);
    }

    public function getAlarmsData($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);
        $alarmsData = $this->buildAlarmsData($turbineId);

        return response()->json($alarmsData);
    }

    /**
     * PRIVATE HELPER METHODS (return arrays, not JSON responses)
     */

    private function buildScadaData($turbineId)
    {
        $scada = ScadaReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$scada) return null;

        return [
            'turbine_id' => $turbineId,
            'latest_reading' => $scada->reading_timestamp,
            'wind_speed_ms' => $scada->wind_speed_ms,
            'power_kw' => $scada->power_kw,
            'rotor_speed_rpm' => $scada->rotor_speed_rpm,
            'generator_speed_rpm' => $scada->generator_speed_rpm,
            'pitch_angle_deg' => $scada->pitch_angle_deg,
            'yaw_angle_deg' => $scada->yaw_angle_deg,
            'nacelle_direction_deg' => $scada->nacelle_direction_deg,
            'ambient_temp_c' => $scada->ambient_temp_c,
            'wind_direction_deg' => $scada->wind_direction_deg,
        ];
    }

    private function buildHydraulicData($turbineId)
    {
        $hydraulic = HydraulicReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$hydraulic) return null;

        $gearboxStatus = $this->service->getGearboxPressureStatus(
            $hydraulic->gearbox_oil_pressure_bar,
            $turbineId
        );

        $hydraulicStatus = $this->service->getHydraulicPressureStatus(
            $hydraulic->hydraulic_pressure_bar
        );

        return [
            'turbine_id' => $turbineId,
            'latest_reading' => $hydraulic->reading_timestamp,
            'gearbox_oil_pressure_bar' => $hydraulic->gearbox_oil_pressure_bar,
            'gearbox_oil_pressure_status' => $gearboxStatus,
            'hydraulic_pressure_bar' => $hydraulic->hydraulic_pressure_bar,
            'hydraulic_pressure_status' => $hydraulicStatus,
        ];
    }

    private function buildVibrationData($turbineId)
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration) return null;

        // ✅ PASS COMPONENT NAMES
        $mainBearingStatus = $this->service->getVibrationStatus(
            $vibration->main_bearing_vibration_rms_mms,
            'main_bearing_vibration_rms'  // ✅ ADD THIS
        );

        $gearboxStatus = $this->service->getVibrationStatus(
            $vibration->gearbox_vibration_axial_mms,
            'gearbox_vibration_axial'  // ✅ ADD THIS
        );

        $generatorStatus = $this->service->getVibrationStatus(
            $vibration->generator_vibration_de_mms,
            'generator_vibration_de'  // ✅ ADD THIS
        );

        $towerStatus = $this->service->getVibrationStatus(
            $vibration->tower_vibration_fa_mms,
            'tower_vibration_fa'  // ✅ ADD THIS
        );

        $bladeStatus = $this->service->getBladeVibrationStatus(
            $vibration->blade1_vibration_mms,
            $vibration->blade2_vibration_mms,
            $vibration->blade3_vibration_mms
        );

        $acousticStatus = $this->service->getAcousticStatus($vibration->acoustic_level_db);

        return [
            'turbine_id' => $turbineId,
            'latest_reading' => $vibration->reading_timestamp,
            'main_bearing_vibration_rms' => $vibration->main_bearing_vibration_rms_mms,
            'main_bearing_vibration_peak' => $vibration->main_bearing_vibration_peak_mms,
            'main_bearing_status' => $mainBearingStatus,
            'gearbox_vibration_axial' => $vibration->gearbox_vibration_axial_mms,
            'gearbox_vibration_radial' => $vibration->gearbox_vibration_radial_mms,
            'gearbox_status' => $gearboxStatus,
            'generator_vibration_de' => $vibration->generator_vibration_de_mms,
            'generator_vibration_nde' => $vibration->generator_vibration_nde_mms,
            'generator_status' => $generatorStatus,
            'tower_vibration_fa' => $vibration->tower_vibration_fa_mms,
            'tower_vibration_ss' => $vibration->tower_vibration_ss_mms,
            'tower_status' => $towerStatus,
            'blade1_vibration' => $vibration->blade1_vibration_mms,
            'blade2_vibration' => $vibration->blade2_vibration_mms,
            'blade3_vibration' => $vibration->blade3_vibration_mms,
            'blade_status' => $bladeStatus,
            'acoustic_level_db' => $vibration->acoustic_level_db,
            'acoustic_status' => $acousticStatus,
            'overall_vibration_status' => $this->service->getOverallVibrationStatus($vibration)
        ];
    }

    private function buildTemperatureData($turbineId)
    {
        $temperature = TemperatureReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$temperature) return null;

        $scada = ScadaReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        $loadFactor = $scada ? min($scada->power_kw / 2500, 1.0) : 0;

        $nacelleStatus = $this->service->getNacelleTemperatureStatus($temperature->nacelle_temp_c);
        $gearboxBearingStatus = $this->service->getGearboxBearingTempStatus(
            $temperature->gearbox_bearing_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $gearboxOilStatus = $this->service->getGearboxOilTempStatus($temperature->gearbox_oil_temp_c);
        $generatorStatus = $this->service->getGeneratorTemperatureStatus(
            $temperature->generator_stator_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $mainBearingStatus = $this->service->getMainBearingTempStatus(
            $temperature->main_bearing_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $generatorBearing1Status = $this->service->getMainBearingTempStatus(
            $temperature->generator_bearing1_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $generatorBearing2Status = $this->service->getMainBearingTempStatus(
            $temperature->generator_bearing2_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );

        return [
            'turbine_id' => $turbineId,
            'latest_reading' => $temperature->reading_timestamp,
            'load_factor' => round($loadFactor, 2),
            'nacelle_temp' => $temperature->nacelle_temp_c,
            'nacelle_status' => $nacelleStatus,
            'gearbox_bearing_temp' => $temperature->gearbox_bearing_temp_c,
            'gearbox_bearing_status' => $gearboxBearingStatus,
            'gearbox_oil_temp' => $temperature->gearbox_oil_temp_c,
            'gearbox_oil_status' => $gearboxOilStatus,
            'generator_bearing1_temp' => $temperature->generator_bearing1_temp_c,
            'generator_bearing1_status' => $generatorBearing1Status,
            'generator_bearing2_temp' => $temperature->generator_bearing2_temp_c,
            'generator_bearing2_status' => $generatorBearing2Status,
            'generator_stator_temp' => $temperature->generator_stator_temp_c,
            'generator_status' => $generatorStatus,
            'main_bearing_temp' => $temperature->main_bearing_temp_c,
            'main_bearing_status' => $mainBearingStatus,
            'overall_temperature_status' => $this->service->getOverallTemperatureStatus($temperature, $loadFactor)
        ];
    }

    private function buildAlarmsData($turbineId)
    {
        $alarms = $this->alarmService->getAllAlarms($turbineId)->map(function($alarm) {
            return [
                'id' => $alarm->id,
                'alarm_code' => $alarm->alarm_code,  // ✅ ADD THIS
                'alarm_type' => $alarm->alarm_type,
                'component' => $alarm->component,
                'severity' => $alarm->severity,
                'status' => $alarm->status,
                'message' => $alarm->message,
                'data' => $alarm->data,
                'detected_at' => $alarm->detected_at,
                'alarm_details' => $alarm->getAlarmDetails(),  // ✅ ADD THIS (includes standard reference)
            ];
        });

        $counts = $this->alarmService->getAlarmCountsBySeverity($turbineId);

        return [
            'turbine_id' => $turbineId,
            'total_alarms' => $alarms->count(),
            'counts_by_severity' => [
                'warning' => $counts['warning'] ?? 0,
                'critical' => $counts['critical'] ?? 0,
                'failed' => $counts['failed'] ?? 0,
            ],
            'alarms' => $alarms
        ];
    }
}
