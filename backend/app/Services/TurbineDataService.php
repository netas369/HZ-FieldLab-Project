<?php

namespace App\Services;

use App\Models\ScadaReading;

class TurbineDataService
{

    /**
     * Get gearbox oil pressure status
     * Based on documentation hints and industry standards
     */
    public function getGearboxPressureStatus($pressure, $turbineId)
    {
        // Based on documentation: 2.0-2.3 bar is warning level (low)
        // Normal should be higher
        $scadaData = ScadaReading::where('turbine_id', $turbineId)->latest('reading_timestamp')->first();
        if ($scadaData->status_code !== 100) {
            return 'Turbine is not running';
        }
        if ($pressure >= 2.5) {
            return [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green'
            ];
        } elseif ($pressure >= 2.0 && $pressure < 2.5) {
            return [
                'status' => 'warning',
                'label' => 'Low Pressure',
                'color' => 'yellow'
            ];
        } elseif ($pressure >= 1.5 && $pressure < 2.0) {
            return [
                'status' => 'critical',
                'label' => 'Very Low Pressure',
                'color' => 'orange'
            ];
        } else {
            return [
                'status' => 'failed',
                'label' => 'Critically Low',
                'color' => 'red'
            ];
        }
    }

    /**
     * Get hydraulic pressure status
     * Based on documentation: ~160 bar is maintained/normal
     */
    public function getHydraulicPressureStatus($pressure)
    {
        // Documentation states: "Hydraulic Pressure: Maintained (~160 bar)"

        if ($pressure >= 150 && $pressure <= 170) {
            return [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green'
            ];
        } elseif ($pressure >= 140 && $pressure < 150) {
            return [
                'status' => 'warning',
                'label' => 'Below Normal',
                'color' => 'yellow'
            ];
        } elseif ($pressure > 170 && $pressure <= 180) {
            return [
                'status' => 'warning',
                'label' => 'Above Normal',
                'color' => 'yellow'
            ];
        } elseif ($pressure >= 120 && $pressure < 140) {
            return [
                'status' => 'critical',
                'label' => 'Low Pressure',
                'color' => 'orange'
            ];
        } elseif ($pressure > 180 && $pressure <= 200) {
            return [
                'status' => 'critical',
                'label' => 'High Pressure',
                'color' => 'orange'
            ];
        } else {
            return [
                'status' => 'failed',
                'label' => 'Pressure Out of Range',
                'color' => 'red'
            ];
        }
    }


    /**
     * Get status severity for UI coloring
     */
    public function getStatusSeverity($statusCode)
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

    public function getStatusDescription($statusCode)
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
    public function getAlarmDescription($alarmCode)
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
    public function getAlarmSeverity($alarmCode)
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
