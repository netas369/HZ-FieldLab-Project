<?php

namespace App\Services;

use App\Models\ScadaReading;
use App\Models\VibrationReading;
use App\Models\TemperatureReading;
use App\Models\HydraulicReading;
use Carbon\Carbon;

/**
 * Retroactive Alarm Analysis Service
 *
 * This service analyzes historical sensor data and identifies what alarms
 * WOULD HAVE occurred based on the threshold logic, even if no alarms
 * were actually created at the time.
 */
class HistoricalAlarmAnalysisService
{
    protected $turbineDataService;

    public function __construct(TurbineDataService $turbineDataService)
    {
        $this->turbineDataService = $turbineDataService;
    }

    /**
     * Analyze historical data and return virtual alarms
     * These are not stored in database - they're generated on-the-fly
     */
    public function analyzeHistoricalPeriod($turbineId, $startDate, $endDate)
    {
        $virtualAlarms = [];

        // Analyze SCADA data
        $virtualAlarms = array_merge(
            $virtualAlarms,
            $this->analyzeScadaHistory($turbineId, $startDate, $endDate)
        );

        // Analyze Vibration data
        $virtualAlarms = array_merge(
            $virtualAlarms,
            $this->analyzeVibrationHistory($turbineId, $startDate, $endDate)
        );

        // Analyze Temperature data
        $virtualAlarms = array_merge(
            $virtualAlarms,
            $this->analyzeTemperatureHistory($turbineId, $startDate, $endDate)
        );

        // Analyze Hydraulic data
        $virtualAlarms = array_merge(
            $virtualAlarms,
            $this->analyzeHydraulicHistory($turbineId, $startDate, $endDate)
        );

        // Sort by timestamp
        usort($virtualAlarms, function($a, $b) {
            return strtotime($a['detected_at']) - strtotime($b['detected_at']);
        });

        // Consolidate consecutive alarms (same component within 1 hour)
        $virtualAlarms = $this->consolidateAlarms($virtualAlarms);

        // Calculate statistics
        $statistics = $this->calculateStatistics($virtualAlarms);

        return [
            'alarms' => $virtualAlarms,
            'statistics' => $statistics,
        ];
    }

    /**
     * Analyze SCADA historical data
     */
    private function analyzeScadaHistory($turbineId, $startDate, $endDate)
    {
        $readings = ScadaReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        $alarms = [];

        foreach ($readings as $reading) {
            // Check Wind Speed - Extreme Weather
            if ($reading->wind_speed_ms > 30.0) {
                $alarms[] = $this->createVirtualAlarm(
                    $turbineId,
                    'scada',
                    'wind_speed',
                    'failed',
                    'Extreme Weather Shutdown',
                    "Wind speed: {$reading->wind_speed_ms} m/s (>30 m/s)",
                    $reading->wind_speed_ms,
                    $reading->reading_timestamp
                );
            }

            // Check Rotor Overspeed
            if ($reading->rotor_speed_rpm > 20) {
                $alarms[] = $this->createVirtualAlarm(
                    $turbineId,
                    'scada',
                    'rotor_speed',
                    'failed',
                    'Rotor Overspeed',
                    "Rotor speed: {$reading->rotor_speed_rpm} RPM (>20 RPM)",
                    $reading->rotor_speed_rpm,
                    $reading->reading_timestamp
                );
            }

            // Check Ambient Temperature
            if ($reading->ambient_temp_c < -20) {
                $alarms[] = $this->createVirtualAlarm(
                    $turbineId,
                    'scada',
                    'ambient_temperature',
                    'critical',
                    'Temperature Too Low',
                    "Ambient: {$reading->ambient_temp_c}°C (<-20°C)",
                    $reading->ambient_temp_c,
                    $reading->reading_timestamp
                );
            } elseif ($reading->ambient_temp_c > 45) {
                $alarms[] = $this->createVirtualAlarm(
                    $turbineId,
                    'scada',
                    'ambient_temperature',
                    'critical',
                    'Temperature Too High',
                    "Ambient: {$reading->ambient_temp_c}°C (>45°C)",
                    $reading->ambient_temp_c,
                    $reading->reading_timestamp
                );
            }
        }

        return $alarms;
    }

    /**
     * Analyze Vibration historical data
     */
    private function analyzeVibrationHistory($turbineId, $startDate, $endDate)
    {
        $readings = VibrationReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        $alarms = [];

        foreach ($readings as $reading) {
            // Check Main Bearing
            $mainBearingStatus = $this->turbineDataService->getVibrationStatus(
                $reading->main_bearing_vibration_rms_mms
            );
            if (in_array($mainBearingStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'vibration',
                    'main_bearing',
                    $mainBearingStatus,
                    $reading->main_bearing_vibration_rms_mms,
                    $reading->reading_timestamp
                );
            }

            // Check Gearbox
            $gearboxVibration = max(
                $reading->gearbox_vibration_axial_mms ?? 0,
                $reading->gearbox_vibration_radial_mms ?? 0
            );
            $gearboxStatus = $this->turbineDataService->getVibrationStatus($gearboxVibration);
            if (in_array($gearboxStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'vibration',
                    'gearbox',
                    $gearboxStatus,
                    $gearboxVibration,
                    $reading->reading_timestamp
                );
            }

            // Check Generator
            $generatorVibration = max(
                $reading->generator_vibration_de_mms ?? 0,
                $reading->generator_vibration_nde_mms ?? 0
            );
            $generatorStatus = $this->turbineDataService->getVibrationStatus($generatorVibration);
            if (in_array($generatorStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'vibration',
                    'generator',
                    $generatorStatus,
                    $generatorVibration,
                    $reading->reading_timestamp
                );
            }

            // Check Tower
            $towerVibration = max(
                $reading->tower_vibration_fa_mms ?? 0,
                $reading->tower_vibration_ss_mms ?? 0
            );
            $towerStatus = $this->turbineDataService->getVibrationStatus($towerVibration);
            if (in_array($towerStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'vibration',
                    'tower',
                    $towerStatus,
                    $towerVibration,
                    $reading->reading_timestamp
                );
            }

            // Check Blade Imbalance
            $bladeStatus = $this->turbineDataService->getBladeVibrationStatus(
                $reading->blade1_vibration_mms,
                $reading->blade2_vibration_mms,
                $reading->blade3_vibration_mms
            );
            if ($bladeStatus['status'] === 'warning') {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'vibration',
                    'blades',
                    $bladeStatus,
                    $bladeStatus['max_vibration'] ?? 0,
                    $reading->reading_timestamp
                );
            }

            // Check Acoustic Level
            $acousticStatus = $this->turbineDataService->getAcousticStatus(
                $reading->acoustic_level_db
            );
            if (in_array($acousticStatus['status'], ['warning', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'vibration',
                    'acoustic',
                    $acousticStatus,
                    $reading->acoustic_level_db,
                    $reading->reading_timestamp
                );
            }
        }

        return array_filter($alarms); // Remove nulls
    }

    /**
     * Analyze Temperature historical data
     */
    private function analyzeTemperatureHistory($turbineId, $startDate, $endDate)
    {
        $readings = TemperatureReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        $alarms = [];

        foreach ($readings as $reading) {
            // Get corresponding SCADA for load factor
            $scada = ScadaReading::where('turbine_id', $turbineId)
                ->where('reading_timestamp', '<=', $reading->reading_timestamp)
                ->orderBy('reading_timestamp', 'desc')
                ->first();

            $loadFactor = $scada ? min($scada->power_kw / 2500, 1.0) : 0;

            // Check Nacelle Temperature
            $nacelleStatus = $this->turbineDataService->getNacelleTemperatureStatus(
                $reading->nacelle_temp_c
            );
            if (in_array($nacelleStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'temperature',
                    'nacelle',
                    $nacelleStatus,
                    $reading->nacelle_temp_c,
                    $reading->reading_timestamp
                );
            }

            // Check Generator Stator
            $generatorStatus = $this->turbineDataService->getGeneratorTemperatureStatus(
                $reading->generator_stator_temp_c,
                $reading->gearbox_oil_temp_c,
                $loadFactor
            );
            if (in_array($generatorStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'temperature',
                    'generator_stator',
                    $generatorStatus,
                    $reading->generator_stator_temp_c,
                    $reading->reading_timestamp
                );
            }

            // Check Gearbox Bearing
            $gearboxBearingStatus = $this->turbineDataService->getGearboxBearingTempStatus(
                $reading->gearbox_bearing_temp_c,
                $reading->gearbox_oil_temp_c,
                $loadFactor
            );
            if (in_array($gearboxBearingStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'temperature',
                    'gearbox_bearing',
                    $gearboxBearingStatus,
                    $reading->gearbox_bearing_temp_c,
                    $reading->reading_timestamp
                );
            }

            // Check Gearbox Oil
            $gearboxOilStatus = $this->turbineDataService->getGearboxOilTempStatus(
                $reading->gearbox_oil_temp_c
            );
            if (in_array($gearboxOilStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'temperature',
                    'gearbox_oil',
                    $gearboxOilStatus,
                    $reading->gearbox_oil_temp_c,
                    $reading->reading_timestamp
                );
            }

            // Check Main Bearing
            $mainBearingStatus = $this->turbineDataService->getMainBearingTempStatus(
                $reading->main_bearing_temp_c,
                $reading->gearbox_oil_temp_c,
                $loadFactor
            );
            if (in_array($mainBearingStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'temperature',
                    'main_bearing',
                    $mainBearingStatus,
                    $reading->main_bearing_temp_c,
                    $reading->reading_timestamp
                );
            }

            // Check Generator Bearings
            $genBearing1Status = $this->turbineDataService->getMainBearingTempStatus(
                $reading->generator_bearing1_temp_c,
                $reading->gearbox_oil_temp_c,
                $loadFactor
            );
            if (in_array($genBearing1Status['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'temperature',
                    'generator_bearing1',
                    $genBearing1Status,
                    $reading->generator_bearing1_temp_c,
                    $reading->reading_timestamp
                );
            }

            $genBearing2Status = $this->turbineDataService->getMainBearingTempStatus(
                $reading->generator_bearing2_temp_c,
                $reading->gearbox_oil_temp_c,
                $loadFactor
            );
            if (in_array($genBearing2Status['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'temperature',
                    'generator_bearing2',
                    $genBearing2Status,
                    $reading->generator_bearing2_temp_c,
                    $reading->reading_timestamp
                );
            }
        }

        return array_filter($alarms);
    }

    /**
     * Analyze Hydraulic historical data
     */
    private function analyzeHydraulicHistory($turbineId, $startDate, $endDate)
    {
        $readings = HydraulicReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        $alarms = [];

        foreach ($readings as $reading) {
            // Check Gearbox Oil Pressure
            $gearboxPressureStatus = $this->turbineDataService->getGearboxPressureStatus(
                $reading->gearbox_oil_pressure_bar,
                $turbineId
            );

            if (is_array($gearboxPressureStatus) && in_array($gearboxPressureStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'hydraulic',
                    'gearbox_oil_pressure',
                    $gearboxPressureStatus,
                    $reading->gearbox_oil_pressure_bar,
                    $reading->reading_timestamp
                );
            }

            // Check Hydraulic Pressure (Pitch System)
            $hydraulicPressureStatus = $this->turbineDataService->getHydraulicPressureStatus(
                $reading->hydraulic_pressure_bar
            );
            if (in_array($hydraulicPressureStatus['status'], ['warning', 'critical', 'failed'])) {
                $alarms[] = $this->createVirtualAlarmFromStatus(
                    $turbineId,
                    'hydraulic',
                    'hydraulic_pressure',
                    $hydraulicPressureStatus,
                    $reading->hydraulic_pressure_bar,
                    $reading->reading_timestamp
                );
            }
        }

        return array_filter($alarms);
    }

    /**
     * Create a virtual alarm structure
     */
    private function createVirtualAlarm($turbineId, $type, $component, $severity, $label, $description, $value, $timestamp)
    {
        $alarmCode = $this->generateAlarmCode($component, $severity, $value);

        return [
            'turbine_id' => $turbineId,
            'alarm_type' => $type,
            'component' => $component,
            'alarm_code' => $alarmCode,
            'severity' => $severity,
            'message' => $this->formatComponentName($component) . ": {$label}",
            'data' => [
                'value' => $value,
                'label' => $label,
                'description' => $description,
            ],
            'detected_at' => $timestamp,
            'is_historical' => true, // Flag to indicate this is retroactive analysis
        ];
    }

    /**
     * Create virtual alarm from status data
     */
    private function createVirtualAlarmFromStatus($turbineId, $type, $component, $statusData, $value, $timestamp)
    {
        $severity = $statusData['status'] ?? null;

        if (!in_array($severity, ['warning', 'critical', 'failed'])) {
            return null;
        }

        $label = $statusData['label'] ?? 'Unknown';
        $description = $statusData['description'] ?? '';

        return $this->createVirtualAlarm(
            $turbineId,
            $type,
            $component,
            $severity,
            $label,
            $description,
            $value,
            $timestamp
        );
    }

    /**
     * Consolidate consecutive alarms for the same component
     * If same component has alarm within 1 hour, treat as one continuous alarm
     */
    private function consolidateAlarms($alarms)
    {
        if (empty($alarms)) {
            return [];
        }

        $consolidated = [];
        $currentAlarm = null;

        foreach ($alarms as $alarm) {
            if ($currentAlarm === null) {
                $currentAlarm = $alarm;
                $currentAlarm['start_time'] = $alarm['detected_at'];
                $currentAlarm['end_time'] = $alarm['detected_at'];
                continue;
            }

            // Check if this is same component and within 1 hour
            $isSameComponent = ($currentAlarm['component'] === $alarm['component']);
            $timeDiff = strtotime($alarm['detected_at']) - strtotime($currentAlarm['end_time']);
            $isWithinHour = ($timeDiff <= 3600); // 1 hour in seconds

            if ($isSameComponent && $isWithinHour) {
                // Extend current alarm
                $currentAlarm['end_time'] = $alarm['detected_at'];
                // Upgrade severity if new alarm is more severe
                if ($this->getSeverityWeight($alarm['severity']) > $this->getSeverityWeight($currentAlarm['severity'])) {
                    $currentAlarm['severity'] = $alarm['severity'];
                    $currentAlarm['message'] = $alarm['message'];
                    $currentAlarm['alarm_code'] = $alarm['alarm_code'];
                }
            } else {
                // Save current alarm and start new one
                $currentAlarm['duration_minutes'] = (strtotime($currentAlarm['end_time']) - strtotime($currentAlarm['start_time'])) / 60;
                $consolidated[] = $currentAlarm;

                $currentAlarm = $alarm;
                $currentAlarm['start_time'] = $alarm['detected_at'];
                $currentAlarm['end_time'] = $alarm['detected_at'];
            }
        }

        // Add last alarm
        if ($currentAlarm !== null) {
            $currentAlarm['duration_minutes'] = (strtotime($currentAlarm['end_time']) - strtotime($currentAlarm['start_time'])) / 60;
            $consolidated[] = $currentAlarm;
        }

        return $consolidated;
    }

    /**
     * Calculate statistics from virtual alarms
     */
    private function calculateStatistics($alarms)
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
            'most_common_components' => [],
            'average_duration_minutes' => 0,
            'total_downtime_hours' => 0,
        ];

        $componentCounts = [];
        $totalDuration = 0;
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

            // Count components
            $component = $alarm['component'];
            if (!isset($componentCounts[$component])) {
                $componentCounts[$component] = 0;
            }
            $componentCounts[$component]++;

            // Calculate duration
            if (isset($alarm['duration_minutes'])) {
                $totalDuration += $alarm['duration_minutes'];

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
        if (count($alarms) > 0) {
            $stats['average_duration_minutes'] = round($totalDuration / count($alarms), 2);
        }

        $stats['total_downtime_hours'] = round($failedDuration / 60, 2);

        return $stats;
    }

    /**
     * Helper methods
     */
    private function generateAlarmCode($component, $severity, $value)
    {
        $baseCode = [
            'warning' => 1000,
            'critical' => 2000,
            'failed' => 3000,
        ][$severity] ?? 9000;

        return $baseCode + (crc32($component) % 100);
    }

    private function formatComponentName($component)
    {
        return ucwords(str_replace('_', ' ', $component));
    }

    private function getSeverityWeight($severity)
    {
        return [
            'warning' => 1,
            'critical' => 2,
            'failed' => 3,
        ][$severity] ?? 0;
    }
}
