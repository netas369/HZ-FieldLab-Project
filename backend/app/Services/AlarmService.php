<?php

namespace App\Services;

use App\Models\Alarm;
use App\Models\ScadaReading;
use App\Models\VibrationReading;
use App\Models\TemperatureReading;
use App\Models\HydraulicReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AlarmService
{
    protected $turbineDataService;

    public function __construct(TurbineDataService $turbineDataService)
    {
        $this->turbineDataService = $turbineDataService;
    }

    /**
     * Main entry point - Check all sensors and create alarms
     */
    public function checkAndCreateAlarms($turbineId)
    {
        $this->checkScadaAlarms($turbineId);
        $this->checkVibrationAlarms($turbineId);
        $this->checkTemperatureAlarms($turbineId);
        $this->checkHydraulicAlarms($turbineId);
    }

    /**
     * Check SCADA data for alarms
     */
    private function checkScadaAlarms($turbineId)
    {
        $scada = ScadaReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$scada) return;

        // Check Wind Speed - Extreme Weather
        if ($scada->wind_speed_ms > 30.0) {
            $this->createAlarm(
                $turbineId,
                'scada',
                'wind_speed',
                'failed',
                'Extreme Weather Shutdown',
                "Wind speed: {$scada->wind_speed_ms} m/s (>30 m/s)",
                $scada->wind_speed_ms,
                $scada->reading_timestamp
            );
        } else {
            $this->autoResolveAlarm($turbineId, 'scada', 'wind_speed');
        }

        // Check Rotor Overspeed
        if ($scada->rotor_speed_rpm > 20) {
            $this->createAlarm(
                $turbineId,
                'scada',
                'rotor_speed',
                'failed',
                'Rotor Overspeed',
                "Rotor speed: {$scada->rotor_speed_rpm} RPM (>20 RPM)",
                $scada->rotor_speed_rpm,
                $scada->reading_timestamp
            );
        } else {
            $this->autoResolveAlarm($turbineId, 'scada', 'rotor_speed');
        }

        // Check Ambient Temperature - Too Low
        if ($scada->ambient_temp_c < -20) {
            $this->createAlarm(
                $turbineId,
                'scada',
                'ambient_temperature',
                'critical',
                'Temperature Too Low',
                "Ambient: {$scada->ambient_temp_c}°C (<-20°C)",
                $scada->ambient_temp_c,
                $scada->reading_timestamp
            );
        } elseif ($scada->ambient_temp_c > 45) {
            // Check Ambient Temperature - Too High
            $this->createAlarm(
                $turbineId,
                'scada',
                'ambient_temperature',
                'critical',
                'Temperature Too High',
                "Ambient: {$scada->ambient_temp_c}°C (>45°C)",
                $scada->ambient_temp_c,
                $scada->reading_timestamp
            );
        } else {
            $this->autoResolveAlarm($turbineId, 'scada', 'ambient_temperature');
        }
    }

    /**
     * Check vibration data for alarms
     */
    private function checkVibrationAlarms($turbineId)
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration) return;

        // Check Main Bearing
        $mainBearingStatus = $this->turbineDataService->getVibrationStatus(
            $vibration->main_bearing_vibration_rms_mms
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'vibration',
            'main_bearing',
            $mainBearingStatus,
            $vibration->main_bearing_vibration_rms_mms,
            $vibration->reading_timestamp
        );

        // Check Gearbox
        $gearboxVibration = max(
            $vibration->gearbox_vibration_axial_mms ?? 0,
            $vibration->gearbox_vibration_radial_mms ?? 0
        );
        $gearboxStatus = $this->turbineDataService->getVibrationStatus($gearboxVibration);
        $this->createAlarmFromStatus(
            $turbineId,
            'vibration',
            'gearbox',
            $gearboxStatus,
            $gearboxVibration,
            $vibration->reading_timestamp
        );

        // Check Generator
        $generatorVibration = max(
            $vibration->generator_vibration_de_mms ?? 0,
            $vibration->generator_vibration_nde_mms ?? 0
        );
        $generatorStatus = $this->turbineDataService->getVibrationStatus($generatorVibration);
        $this->createAlarmFromStatus(
            $turbineId,
            'vibration',
            'generator',
            $generatorStatus,
            $generatorVibration,
            $vibration->reading_timestamp
        );

        // Check Tower
        $towerVibration = max(
            $vibration->tower_vibration_fa_mms ?? 0,
            $vibration->tower_vibration_ss_mms ?? 0
        );
        $towerStatus = $this->turbineDataService->getVibrationStatus($towerVibration);
        $this->createAlarmFromStatus(
            $turbineId,
            'vibration',
            'tower',
            $towerStatus,
            $towerVibration,
            $vibration->reading_timestamp
        );

        // ✅ ADD: Check Blade Imbalance
        $bladeStatus = $this->turbineDataService->getBladeVibrationStatus(
            $vibration->blade1_vibration_mms,
            $vibration->blade2_vibration_mms,
            $vibration->blade3_vibration_mms
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'vibration',
            'blades',
            $bladeStatus,
            $bladeStatus['max_vibration'] ?? 0,
            $vibration->reading_timestamp
        );

        // ✅ ADD: Check Acoustic Level
        $acousticStatus = $this->turbineDataService->getAcousticStatus(
            $vibration->acoustic_level_db
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'vibration',
            'acoustic',
            $acousticStatus,
            $vibration->acoustic_level_db,
            $vibration->reading_timestamp
        );
    }

    /**
     * Check temperature data for alarms
     */
    private function checkTemperatureAlarms($turbineId)
    {
        $temperature = TemperatureReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$temperature) return;

        // Get load factor for temperature calculations
        $scada = ScadaReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();
        $loadFactor = $scada ? min($scada->power_kw / 2500, 1.0) : 0;

        // ✅ ADD: Check Nacelle Temperature
        $nacelleStatus = $this->turbineDataService->getNacelleTemperatureStatus(
            $temperature->nacelle_temp_c
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'temperature',
            'nacelle',
            $nacelleStatus,
            $temperature->nacelle_temp_c,
            $temperature->reading_timestamp
        );

        // Check Generator Stator
        $generatorStatus = $this->turbineDataService->getGeneratorTemperatureStatus(
            $temperature->generator_stator_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'temperature',
            'generator_stator',
            $generatorStatus,
            $temperature->generator_stator_temp_c,
            $temperature->reading_timestamp
        );

        // ✅ ADD: Check Generator Bearing 1
        $generatorBearing1Status = $this->turbineDataService->getMainBearingTempStatus(
            $temperature->generator_bearing1_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'temperature',
            'generator_bearing1',
            $generatorBearing1Status,
            $temperature->generator_bearing1_temp_c,
            $temperature->reading_timestamp
        );

        // ✅ ADD: Check Generator Bearing 2
        $generatorBearing2Status = $this->turbineDataService->getMainBearingTempStatus(
            $temperature->generator_bearing2_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'temperature',
            'generator_bearing2',
            $generatorBearing2Status,
            $temperature->generator_bearing2_temp_c,
            $temperature->reading_timestamp
        );

        // Check Gearbox Bearing
        $gearboxBearingStatus = $this->turbineDataService->getGearboxBearingTempStatus(
            $temperature->gearbox_bearing_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'temperature',
            'gearbox_bearing',
            $gearboxBearingStatus,
            $temperature->gearbox_bearing_temp_c,
            $temperature->reading_timestamp
        );

        // Check Gearbox Oil
        $gearboxOilStatus = $this->turbineDataService->getGearboxOilTempStatus(
            $temperature->gearbox_oil_temp_c
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'temperature',
            'gearbox_oil',
            $gearboxOilStatus,
            $temperature->gearbox_oil_temp_c,
            $temperature->reading_timestamp
        );

        // Check Main Bearing
        $mainBearingStatus = $this->turbineDataService->getMainBearingTempStatus(
            $temperature->main_bearing_temp_c,
            $temperature->gearbox_oil_temp_c,
            $loadFactor
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'temperature',
            'main_bearing',
            $mainBearingStatus,
            $temperature->main_bearing_temp_c,
            $temperature->reading_timestamp
        );
    }

    /**
     * Check hydraulic data for alarms
     */
    private function checkHydraulicAlarms($turbineId)
    {
        $hydraulic = HydraulicReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$hydraulic) return;

        // Check Gearbox Oil Pressure
        $gearboxPressureStatus = $this->turbineDataService->getGearboxPressureStatus(
            $hydraulic->gearbox_oil_pressure_bar,
            $turbineId
        );

        // Only check if turbine is running (not a string response)
        if (is_array($gearboxPressureStatus)) {
            $this->createAlarmFromStatus(
                $turbineId,
                'hydraulic',
                'gearbox_oil_pressure',
                $gearboxPressureStatus,
                $hydraulic->gearbox_oil_pressure_bar,
                $hydraulic->reading_timestamp
            );
        }

        // Check Hydraulic Pressure (Pitch System)
        $hydraulicPressureStatus = $this->turbineDataService->getHydraulicPressureStatus(
            $hydraulic->hydraulic_pressure_bar
        );
        $this->createAlarmFromStatus(
            $turbineId,
            'hydraulic',
            'hydraulic_pressure',
            $hydraulicPressureStatus,
            $hydraulic->hydraulic_pressure_bar,
            $hydraulic->reading_timestamp
        );
    }

    /**
     * Create alarm from status array (from TurbineDataService)
     */
    private function createAlarmFromStatus($turbineId, $type, $component, $statusData, $value, $timestamp)
    {
        $severity = $statusData['status'] ?? null;

        // Only create alarms for warning, critical, or failed
        if (!in_array($severity, ['warning', 'critical', 'failed'])) {
            $this->autoResolveAlarm($turbineId, $type, $component);
            return;
        }

        $label = $statusData['label'] ?? 'Unknown';
        $description = $statusData['description'] ?? '';

        $this->createAlarm(
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
     * Core method to create or update alarm
     */
    private function createAlarm($turbineId, $type, $component, $severity, $label, $description, $value, $timestamp)
    {
        // Check if alarm already exists
        $existingAlarm = Alarm::where('turbine_id', $turbineId)
            ->where('alarm_type', $type)
            ->where('component', $component)
            ->where('status', 'active')
            ->first();

        // If alarm exists with same severity, just update it
        if ($existingAlarm && $existingAlarm->severity === $severity) {
            $existingAlarm->update([
                'data' => [
                    'value' => $value,
                    'label' => $label,
                    'description' => $description,
                ],
                'detected_at' => $timestamp ?? Carbon::now(),
            ]);
            return;
        }

        // If severity changed, resolve old and create new
        if ($existingAlarm) {
            $existingAlarm->update([
                'status' => 'resolved',
                'resolved_at' => Carbon::now(),
                'resolution_notes' => "Auto-resolved: Severity changed from {$existingAlarm->severity} to {$severity}"
            ]);
        }

        // Create new alarm
        $message = $this->generateAlarmMessage($type, $component, [
            'label' => $label,
            'description' => $description
        ]);

        $alarm = Alarm::create([
            'turbine_id' => $turbineId,
            'alarm_type' => $type,
            'component' => $component,
            'severity' => $severity,
            'status' => 'active',
            'message' => $message,
            'data' => [
                'value' => $value,
                'label' => $label,
                'description' => $description,
            ],
            'detected_at' => $timestamp ?? Carbon::now(),
        ]);

        Log::info("Alarm created: {$type} - {$component} for turbine {$turbineId}", [
            'severity' => $severity,
            'alarm_id' => $alarm->id,
            'value' => $value
        ]);
    }

    /**
     * Auto-resolve alarm when component returns to normal
     */
    protected function autoResolveAlarm($turbineId, $alarmType, $component)
    {
        $activeAlarm = Alarm::where('turbine_id', $turbineId)
            ->where('alarm_type', $alarmType)
            ->where('component', $component)
            ->where('status', 'active')
            ->first();

        if ($activeAlarm) {
            $activeAlarm->update([
                'status' => 'resolved',
                'resolved_at' => Carbon::now(),
                'resolution_notes' => 'Auto-resolved: Component returned to normal operation'
            ]);

            Log::info("Alarm auto-resolved: {$alarmType} - {$component} for turbine {$turbineId}");
        }
    }

    /**
     * Generate human-readable alarm message
     */
    protected function generateAlarmMessage($alarmType, $component, $statusData)
    {
        $label = $statusData['label'] ?? 'Unknown';
        $description = $statusData['description'] ?? '';

        $componentName = $this->formatComponentName($component);

        return "{$componentName}: {$label}" . ($description ? " - {$description}" : '');
    }

    /**
     * Format component name for display
     */
    protected function formatComponentName($component)
    {
        return ucwords(str_replace('_', ' ', $component));
    }

    /**
     * Get active alarms for a turbine
     */
    public function getActiveAlarms($turbineId, $severity = null)
    {
        $query = Alarm::where('turbine_id', $turbineId)
            ->where('status', 'active')
            ->orderBy('severity', 'desc')
            ->orderBy('detected_at', 'desc');

        if ($severity) {
            $query->where('severity', $severity);
        }

        return $query->get();
    }

    /**
     * Get alarm count by severity for a turbine
     */
    public function getAlarmCountsBySeverity($turbineId)
    {
        return Alarm::where('turbine_id', $turbineId)
            ->where('status', 'active')
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();
    }

    /**
     * Acknowledge an alarm
     */
    public function acknowledgeAlarm($alarmId, $userId)
    {
        $alarm = Alarm::findOrFail($alarmId);

        $alarm->update([
            'status' => 'acknowledged',
            'acknowledged_at' => Carbon::now(),
            'acknowledged_by' => $userId,
        ]);

        return $alarm;
    }

    /**
     * Resolve an alarm manually
     */
    public function resolveAlarm($alarmId, $userId, $notes = null)
    {
        $alarm = Alarm::findOrFail($alarmId);

        $alarm->update([
            'status' => 'resolved',
            'resolved_at' => Carbon::now(),
            'resolved_by' => $userId,
            'resolution_notes' => $notes,
        ]);

        return $alarm;
    }
}
