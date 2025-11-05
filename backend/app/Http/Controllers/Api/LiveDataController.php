<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScadaReading;
use App\Models\Turbine;
use Illuminate\Http\Request;

class LiveDataController extends Controller
{
    public function getTurbineLatestScadaData($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);
        $scadaData = ScadaReading::where('turbine_id', $turbine->id)->latest('reading_timestamp')->first();

        return response()->json([
            'turbine_id' => $turbine->id,
            'latest_reading' => $scadaData->reading_timestamp,
            'wind_speed_ms' => $scadaData->wind_speed_ms,
            'power_kw' => $scadaData->power_kw,
            'rotor_speed_rpm' => $scadaData->rotor_speed_rpm,
            'generator_speed_rpm' => $scadaData->generator_speed_rpm,
            'pitch_angle_deg' => $scadaData->pitch_angle_deg,
            'yaw_angle_deg' => $scadaData->yaw_angle_deg,
            'nacelle_direction_deg' => $scadaData->nacelle_direction_deg,
            'ambient_temp_c' => $scadaData->ambient_temp_c,
            'wind_direction_deg' => $scadaData->wind_direction_deg,
            'status_code' => $scadaData->status_code,
            'status_severity' => $this->getStatusSeverity($scadaData->status_code),
            'status_description' => $this->getStatusDescription($scadaData->status_code),
            'alarm_code' => $scadaData->alarm_code,
            'alarm_description' => $this->getAlarmDescription($scadaData->alarm_code),
            'alarm_severity' => $this->getAlarmSeverity($scadaData->alarm_code),
        ]);
    }

    /**
     * Get status severity for UI coloring
     */
    private function getStatusSeverity($statusCode)
    {
        $severity = [
            100 => 'normal',    // Green
            200 => 'idle',      // Blue
            300 => 'maintenance', // Yellow
            400 => 'critical',  // Red
            500 => 'external',  // Orange
        ];

        return $severity[$statusCode] ?? 'unknown';
    }

    private function getStatusDescription($statusCode)
    {
        $statuses = [
            100 => 'Normal Operation',
            200 => 'Idle',
            300 => 'Maintenance Mode',
            400 => 'Fault',
            500 => 'Grid Fault',
        ];

        return $statuses[$statusCode] ?? 'Unknown Status';
    }

    /**
     * Get alarm description based on actual documentation
     */
    private function getAlarmDescription($alarmCode)
    {
        if (!$alarmCode || $alarmCode == 0) {
            return null;
        }

        $alarms = [
            // WARNING ALARMS (1000-1999) - Level 1
            1001 => 'Main Bearing Wear Warning',
            1002 => 'Gearbox Oil Quality Warning',
            1003 => 'Blade Imbalance Detected',
            1004 => 'Generator Overheating Warning',
            1005 => 'Yaw System Response Degraded',

            // CRITICAL ALARMS (2000-2999) - Level 2
            2001 => 'Main Bearing Critical - Urgent Maintenance Required',
            2002 => 'Gearbox Critical - Oil Change Required Urgently',
            2003 => 'Blade System Critical',
            2004 => 'Generator Critical - Urgent Service Required',

            // FAILED COMPONENT ALARMS (3000-3999) - Level 3
            3001 => 'Main Bearing Failed - Emergency Shutdown',
            3002 => 'Gearbox Failed - Component Replacement Required',
            3003 => 'Pitch System Failed - Emergency Shutdown',
            3004 => 'Generator Failed - Component Replacement Required',

            // GRID & EXTERNAL ALARMS (5000-5999) - Level 5
            5001 => 'Grid Fault - External Issue',
        ];

        return $alarms[$alarmCode] ?? "Unknown Alarm Code ($alarmCode)";
    }

    /**
     * Get alarm severity level
     */
    private function getAlarmSeverity($alarmCode)
    {
        if (!$alarmCode || $alarmCode == 0) {
            return 'none';
        }

        // Determine severity by alarm code range
        if ($alarmCode >= 1001 && $alarmCode <= 1999) {
            return 'warning';   // Yellow - Level 1
        } elseif ($alarmCode >= 2001 && $alarmCode <= 2999) {
            return 'critical';  // Orange - Level 2
        } elseif ($alarmCode >= 3001 && $alarmCode <= 3999) {
            return 'failed';    // Red - Level 3
        } elseif ($alarmCode >= 5001 && $alarmCode <= 5999) {
            return 'external';  // Orange - Level 5
        }

        return 'unknown';
    }
}
