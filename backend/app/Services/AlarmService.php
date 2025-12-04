<?php

namespace App\Services;

use App\Constants\AlarmCodes;
use App\Enums\TurbineStatus;
use App\Models\Alarm;
use App\Models\ScadaReading;
use App\Models\Turbine;
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

    public function updateTurbineStatus($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);

        // ============================================
        // EDGE CASE 1: No SCADA data at all
        // ============================================
        $scada = ScadaReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$scada) {
            // No data available - communication issue
            $turbine->status = TurbineStatus::Error;
            $turbine->save();
            Log::warning("No SCADA data found for turbine {$turbineId}");
            return;
        }

        // ============================================
        // EDGE CASE 2: Stale/old data (no recent readings)
        // ============================================
//        $lastReadingAge = Carbon::now()->diffInMinutes($scada->reading_timestamp);
//
//        if ($lastReadingAge > 60) {
//            // Data is older than 1 hour - possible communication failure
//            $turbine->status = TurbineStatus::Error;
//            $turbine->save();
//            Log::warning("Stale SCADA data for turbine {$turbineId}. Last reading: {$scada->reading_timestamp}");
//            return;
//        }

        // ============================================
        // EDGE CASE 3: Check for GRID FAULT alarms first
        // ============================================
        $gridFaultAlarms = Alarm::where('turbine_id', $turbineId)
            ->where('status', 'active')
            ->where('alarm_type', 'grid')  // If you have grid-specific alarms
            ->exists();

        if ($gridFaultAlarms) {
            $turbine->status = TurbineStatus::GridFault;
            $turbine->save();
            return;
        }

        // ============================================
        // EDGE CASE 4: Check for MAINTENANCE MODE
        // (You might want to add a manual override for this)
        // ============================================
        $maintenanceAlarms = Alarm::where('turbine_id', $turbineId)
            ->where('status', 'active')
            ->where('component', 'maintenance_scheduled')  // If you track scheduled maintenance
            ->exists();

        if ($maintenanceAlarms) {
            $turbine->status = TurbineStatus::Maintenance;
            $turbine->save();
            return;
        }

        // ============================================
        // MAIN LOGIC: Check for failed alarms
        // ============================================
        $failedAlarms = Alarm::where('turbine_id', $turbineId)
            ->where('status', 'active')
            ->where('severity', 'failed')
            ->get();

        if ($failedAlarms->isNotEmpty()) {
            // EDGE CASE 5: Multiple failed alarms - prioritize component failures
            $componentFailures = $failedAlarms->whereIn('component', [
                'main_bearing',
                'gearbox',
                'generator_stator',
                'generator_bearing1',
                'generator_bearing2',
                'rotor_speed',
                'hydraulic_pressure',      // Pitch system failure
                'gearbox_oil_pressure'     // Critical lubrication failure
            ]);

            if ($componentFailures->isNotEmpty()) {
                // Component failure takes priority over environmental
                $turbine->status = TurbineStatus::Error; // FAULT - Component broken
            } else {
                // Only environmental failures (wind, temperature)
                $turbine->status = TurbineStatus::Idle; // IDLE - Environmental
            }
        }
        // ============================================
        // EDGE CASE 6: No failed alarms, check wind conditions
        // ============================================
        elseif ($scada->wind_speed_ms < 3.0 || $scada->wind_speed_ms > 25.0) {
            // Wind outside operational range
            $turbine->status = TurbineStatus::Idle; // IDLE - Waiting for wind
        }
        // ============================================
        // EDGE CASE 7: Check for critical alarms (not failed, but serious)
        // ============================================
        elseif ($this->hasCriticalConditions($turbineId)) {
            // Has critical warnings but still operational with limits
            $turbine->status = TurbineStatus::Normal; // Still operational but monitored
        }
        // ============================================
        // DEFAULT: All systems normal
        // ============================================
        else {
            $turbine->status = TurbineStatus::Normal; // OPERATIONAL
        }

        $turbine->save();

        Log::info("Turbine {$turbineId} status updated to: {$turbine->status->label()}");
    }

    /**
     * Helper: Check if turbine has critical conditions that limit operation
     */
    private function hasCriticalConditions($turbineId): bool
    {
        return Alarm::where('turbine_id', $turbineId)
            ->where('status', 'active')
            ->where('severity', 'critical')
            ->exists();
    }

//    public function updateTurbineStatus($turbineId)
//    {
//        $turbine = Turbine::findOrFail($turbineId);
//
//        // Check for SCADA wind conditions first
//        $scada = ScadaReading::where('turbine_id', $turbineId)
//            ->latest('reading_timestamp')
//            ->first();
//
//        if (!$scada) {
//            // No data available - communication issue
//            $turbine->status = TurbineStatus::Error;
//            $turbine->save();
//            Log::warning("No SCADA data found for turbine {$turbineId}");
//            return;
//        }
//
//        $gridFaultAlarms = Alarm::where('turbine_id', $turbineId)
//            ->where('status', 'active')
//            ->where('alarm_type', 'grid')  // If you have grid-specific alarms
//            ->exists();
//
//        if ($gridFaultAlarms) {
//            $turbine->status = TurbineStatus::GridFault;
//            $turbine->save();
//            return;
//        }
//
//        $maintenanceAlarms = Alarm::where('turbine_id', $turbineId)
//            ->where('status', 'active')
//            ->where('component', 'maintenance_scheduled')  // If you track scheduled maintenance
//            ->exists();
//
//        if ($maintenanceAlarms) {
//            $turbine->status = TurbineStatus::Maintenance;
//            $turbine->save();
//            return;
//        }
//
//        // Check for failed alarms
//        $failedAlarms = Alarm::where('turbine_id', $turbineId)
//            ->where('status', 'active')
//            ->where('severity', 'failed')
//            ->get();
//
//        if ($failedAlarms->isNotEmpty()) {
//            // Component failures vs environmental failures
//            $componentFailures = $failedAlarms->whereIn('component', [
//                'main_bearing',
//                'gearbox',
//                'generator_stator',
//                'generator_bearing1',
//                'generator_bearing2',
//                'rotor_speed',
//                'hydraulic_pressure',      // Pitch system failure
//                'gearbox_oil_pressure'     // Critical lubrication failure
//            ]);
//
//            if ($componentFailures->isNotEmpty()) {
//                // Component failure takes priority over environmental
//                $turbine->status = TurbineStatus::Error; // FAULT - Component broken
//            } else {
//                // Only environmental failures (wind, temperature)
//                $turbine->status = TurbineStatus::Idle; // IDLE - Environmental
//            }
//        } elseif ($scada && ($scada->wind_speed_ms < 3.0 || $scada->wind_speed_ms > 25.0)) {
//            // No failed alarms, but wind is outside operational range
//            $turbine->status = TurbineStatus::Idle; // IDLE - Waiting for wind
//        } else {
//            // No alarms, wind is good → Running normally
//            $turbine->status_code = TurbineStatus::Normal; // OPERATIONAL
//        }
//
//        $turbine->save();
//    }

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

        // Check Blade Imbalance
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

        // Check Acoustic Level
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

        // Check Nacelle Temperature
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

        // Check Generator Bearing 1
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

        // Check Generator Bearing 2
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
        // Get the appropriate alarm code
        $alarmCode = AlarmCodes::getCodeForComponent($component, $severity, $value);

        // If no specific code found, generate based on severity
        if (!$alarmCode) {
            $alarmCode = $this->generateFallbackCode($component, $severity);
        }

        // Check if alarm already exists
        $existingAlarm = Alarm::where('turbine_id', $turbineId)
            ->where('alarm_type', $type)
            ->where('component', $component)
            ->where('status', 'active')
            ->first();

        if ($existingAlarm && $existingAlarm->severity === $severity) {
            $existingAlarm->update([
                'alarm_code' => $alarmCode,  // ✅ ADD THIS
                'data' => [
                    'value' => $value,
                    'label' => $label,
                    'description' => $description,
                ],
                'detected_at' => $timestamp ?? Carbon::now(),
            ]);
            return;
        }

        if ($existingAlarm) {
            $existingAlarm->update([
                'status' => 'resolved',
                'resolved_at' => Carbon::now(),
                'resolution_notes' => "Auto-resolved: Severity changed from {$existingAlarm->severity} to {$severity}"
            ]);
        }

        $message = $this->generateAlarmMessage($type, $component, [
            'label' => $label,
            'description' => $description
        ]);

        $alarm = Alarm::create([
            'turbine_id' => $turbineId,
            'alarm_type' => $type,
            'component' => $component,
            'alarm_code' => $alarmCode,  // ✅ ADD THIS
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

        Log::info("Alarm created: {$type} - {$component} (Code: {$alarmCode}) for turbine {$turbineId}", [
            'severity' => $severity,
            'alarm_id' => $alarm->id,
            'alarm_code' => $alarmCode,
            'value' => $value
        ]);
    }

    private function generateFallbackCode($component, $severity): int
    {
        // Fallback code generation if specific code not found
        $baseCode = [
            'warning' => 1000,
            'critical' => 2000,
            'failed' => 3000,
        ][$severity] ?? 9000;

        return $baseCode + crc32($component) % 100;
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
