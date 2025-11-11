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

    /**
     * Get vibration status based on ISO 10816 standard
     */
    public function getVibrationStatus($vibration_mms)
    {
        if ($vibration_mms === null) {
            return [
                'status' => 'unknown',
                'label' => 'No Data',
                'zone' => 'N/A',
                'color' => 'gray',
                'severity' => 'none'
            ];
        }

        // ISO 10816 Zones for large machines (wind turbines)
        if ($vibration_mms < 2.8) {
            return [
                'status' => 'excellent',
                'label' => 'Excellent',
                'zone' => 'Zone A',
                'color' => 'green',
                'severity' => 'none',
                'description' => 'Newly commissioned condition'
            ];
        } elseif ($vibration_mms >= 2.8 && $vibration_mms < 7.1) {
            return [
                'status' => 'good',
                'label' => 'Good',
                'zone' => 'Zone B',
                'color' => 'green',
                'severity' => 'none',
                'description' => 'Acceptable for long-term operation'
            ];
        } elseif ($vibration_mms >= 7.1 && $vibration_mms < 11.2) {
            return [
                'status' => 'warning',
                'label' => 'Warning',
                'zone' => 'Zone C',
                'color' => 'yellow',
                'severity' => 'warning',
                'description' => 'Plan maintenance soon'
            ];
        } else {
            return [
                'status' => 'critical',
                'label' => 'Critical',
                'zone' => 'Zone D',
                'color' => 'red',
                'severity' => 'critical',
                'description' => 'Unacceptable - immediate action required'
            ];
        }
    }

    /**
     * Check blade vibration balance
     */
    public function getBladeVibrationStatus($blade1, $blade2, $blade3)
    {
        if ($blade1 === null || $blade2 === null || $blade3 === null) {
            return [
                'status' => 'unknown',
                'label' => 'No Data',
                'color' => 'gray',
                'balanced' => null
            ];
        }

        // Check individual blade vibrations
        $maxVibration = max($blade1, $blade2, $blade3);
        $avgVibration = ($blade1 + $blade2 + $blade3) / 3;

        // Check if blades are balanced (difference < 30% from average)
        $imbalance = abs($maxVibration - $avgVibration) / $avgVibration;
        $isBalanced = $imbalance < 0.3;

        // Get overall status
        $status = $this->getVibrationStatus($maxVibration);

        return [
            'status' => $status['status'],
            'label' => $status['label'],
            'zone' => $status['zone'],
            'color' => $status['color'],
            'balanced' => $isBalanced,
            'imbalance_percentage' => round($imbalance * 100, 1),
            'max_vibration' => $maxVibration,
            'avg_vibration' => round($avgVibration, 2),
            'blade_values' => [
                'blade1' => $blade1,
                'blade2' => $blade2,
                'blade3' => $blade3
            ]
        ];
    }

    /**
     * Get acoustic level status
     */
    public function getAcousticStatus($db_level)
    {
        if ($db_level === null) {
            return [
                'status' => 'unknown',
                'label' => 'No Data',
                'color' => 'gray'
            ];
        }

        // Typical wind turbine noise levels
        if ($db_level < 45) {
            return [
                'status' => 'quiet',
                'label' => 'Quiet',
                'color' => 'green',
                'description' => 'Normal operation'
            ];
        } elseif ($db_level >= 45 && $db_level < 55) {
            return [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green',
                'description' => 'Typical noise level'
            ];
        } elseif ($db_level >= 55 && $db_level < 65) {
            return [
                'status' => 'elevated',
                'label' => 'Elevated',
                'color' => 'yellow',
                'description' => 'Higher than usual'
            ];
        } else {
            return [
                'status' => 'loud',
                'label' => 'Loud',
                'color' => 'orange',
                'description' => 'Investigate noise source'
            ];
        }
    }

    /**
     * Get overall vibration health assessment
     */
    public function getOverallVibrationStatus($vibration)
    {
        $components = [
            'main_bearing' => $vibration->main_bearing_vibration_rms_mms,
            'gearbox' => max(
                $vibration->gearbox_vibration_axial_mms ?? 0,
                $vibration->gearbox_vibration_radial_mms ?? 0
            ),
            'generator' => max(
                $vibration->generator_vibration_de_mms ?? 0,
                $vibration->generator_vibration_nde_mms ?? 0
            ),
            'tower' => max(
                $vibration->tower_vibration_fa_mms ?? 0,
                $vibration->tower_vibration_ss_mms ?? 0
            ),
        ];

        $criticalCount = 0;
        $warningCount = 0;

        foreach ($components as $name => $value) {
            if ($value >= 11.2) {
                $criticalCount++;
            } elseif ($value >= 7.1) {
                $warningCount++;
            }
        }

        if ($criticalCount > 0) {
            return [
                'status' => 'critical',
                'label' => 'Critical Vibration Detected',
                'color' => 'red',
                'message' => "$criticalCount component(s) in critical zone"
            ];
        } elseif ($warningCount > 0) {
            return [
                'status' => 'warning',
                'label' => 'Elevated Vibration',
                'color' => 'yellow',
                'message' => "$warningCount component(s) need attention"
            ];
        } else {
            return [
                'status' => 'good',
                'label' => 'All Components Normal',
                'color' => 'green',
                'message' => 'All vibrations within acceptable range'
            ];
        }
    }

    /**
     * Get nacelle temperature status
     */
    public function getNacelleTemperatureStatus($temp)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        // Nacelle should be slightly above ambient (equipment heat)
        if ($temp < 0) {
            return $this->getStatusStructure('critical', 'Very Cold', 'orange', 'Below operating temperature');
        } elseif ($temp >= 0 && $temp < 50) {
            return $this->getStatusStructure('good', 'Normal', 'green', 'Acceptable nacelle temperature');
        } elseif ($temp >= 50 && $temp < 70) {
            return $this->getStatusStructure('warning', 'Warm', 'yellow', 'Higher than typical');
        } else {
            return $this->getStatusStructure('critical', 'Hot', 'red', 'Excessive nacelle heat');
        }
    }

    /**
     * Get gearbox bearing temperature status
     */
    public function getGearboxBearingTempStatus($temp, $ambient, $loadFactor)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        // Expected: ambient + load factor effect
        $expectedTemp = $ambient + (30 * $loadFactor);
        $deviation = $temp - $expectedTemp;

        // From docs: +5 to +15°C above expected is warning
        if ($deviation < 5) {
            return $this->getStatusStructure('good', 'Normal', 'green', 'Within expected range');
        } elseif ($deviation >= 5 && $deviation < 15) {
            return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Monitor - trending warm');
        } elseif ($deviation >= 15 && $deviation < 25) {
            return $this->getStatusStructure('critical', 'High', 'orange', 'Urgent - plan maintenance');
        } else {
            return $this->getStatusStructure('failed', 'Very High', 'red', 'Critical overheating');
        }
    }

    /**
     * Get gearbox oil temperature status
     */
    public function getGearboxOilTempStatus($temp)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        // Typical gearbox oil operating temperature
        if ($temp < 40) {
            return $this->getStatusStructure('good', 'Cold', 'green', 'Starting up or low load');
        } elseif ($temp >= 40 && $temp < 70) {
            return $this->getStatusStructure('good', 'Normal', 'green', 'Optimal operating temperature');
        } elseif ($temp >= 70 && $temp < 80) {
            return $this->getStatusStructure('warning', 'Warm', 'yellow', 'Higher than optimal');
        } elseif ($temp >= 80 && $temp < 90) {
            return $this->getStatusStructure('critical', 'Hot', 'orange', 'Check cooling system');
        } else {
            return $this->getStatusStructure('failed', 'Very Hot', 'red', 'Oil degradation risk');
        }
    }

    /**
     * Get generator temperature status (from documentation)
     */
    public function getGeneratorTemperatureStatus($statorTemp, $ambient, $loadFactor)
    {
        if ($statorTemp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        // From docs: Expected = Ambient + (55 × load_factor)
        $expectedTemp = $ambient + (55 * $loadFactor);
        $deviation = $expectedTemp - $statorTemp;

        // From documentation thresholds
        if ($statorTemp < 100) {
            if ($deviation > -10) {
                return $this->getStatusStructure('good', 'Normal', 'green', 'Within expected range');
            } else {
                return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Above expected by ' . abs(round($deviation)) . '°C');
            }
        } elseif ($statorTemp >= 100 && $statorTemp < 110) {
            return $this->getStatusStructure('warning', 'High (Limit to 80% Power)', 'yellow', 'Generator warming - reduce load');
        } elseif ($statorTemp >= 110 && $statorTemp < 115) {
            return $this->getStatusStructure('critical', 'Very High (Limit to 60% Power)', 'orange', 'Critical - implement power curtailment');
        } elseif ($statorTemp >= 115 && $statorTemp < 120) {
            return $this->getStatusStructure('critical', 'Shutdown Temperature', 'red', 'Shutdown to prevent damage');
        } else {
            return $this->getStatusStructure('failed', 'Failed - Overheated', 'red', 'Component failure - Alarm 3004');
        }
    }

    /**
     * Get main bearing temperature status
     */
    public function getMainBearingTempStatus($temp, $ambient, $loadFactor)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        $expectedTemp = $ambient + (25 * $loadFactor);
        $deviation = $temp - $expectedTemp;

        // From docs: +5 to +15°C above expected is warning
        if ($deviation < 5) {
            return $this->getStatusStructure('good', 'Normal', 'green', 'Within expected range');
        } elseif ($deviation >= 5 && $deviation < 15) {
            return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Monitor bearing condition');
        } elseif ($deviation >= 15 && $deviation < 25) {
            return $this->getStatusStructure('critical', 'High', 'orange', 'Check lubrication system');
        } else {
            return $this->getStatusStructure('failed', 'Very High', 'red', 'Bearing damage risk');
        }
    }

    /**
     * Get overall temperature assessment
     */
    public function getOverallTemperatureStatus($temperature, $loadFactor)
    {
        $criticalComponents = [];
        $warningComponents = [];

        // Check each component
        $checks = [
            'Generator Stator' => $temperature->generator_stator_temp_c,
            'Gearbox Bearing' => $temperature->gearbox_bearing_temp_c,
            'Gearbox Oil' => $temperature->gearbox_oil_temp_c,
            'Main Bearing' => $temperature->main_bearing_temp_c,
        ];

        foreach ($checks as $component => $temp) {
            if ($temp === null) continue;

            // Apply component-specific thresholds
            if ($component === 'Generator Stator') {
                if ($temp >= 115) $criticalComponents[] = $component;
                elseif ($temp >= 100) $warningComponents[] = $component;
            } elseif ($component === 'Gearbox Oil') {
                if ($temp >= 90) $criticalComponents[] = $component;
                elseif ($temp >= 80) $warningComponents[] = $component;
            } else {
                // Bearings
                if ($temp >= 100) $criticalComponents[] = $component;
                elseif ($temp >= 80) $warningComponents[] = $component;
            }
        }

        if (count($criticalComponents) > 0) {
            return [
                'status' => 'critical',
                'label' => 'Critical Temperature Detected',
                'color' => 'red',
                'message' => 'Components overheating: ' . implode(', ', $criticalComponents),
                'action_required' => 'Immediate inspection required'
            ];
        } elseif (count($warningComponents) > 0) {
            return [
                'status' => 'warning',
                'label' => 'Elevated Temperatures',
                'color' => 'yellow',
                'message' => 'Monitor: ' . implode(', ', $warningComponents),
                'action_required' => 'Check cooling systems'
            ];
        } else {
            return [
                'status' => 'good',
                'label' => 'All Temperatures Normal',
                'color' => 'green',
                'message' => 'All components within acceptable range',
                'action_required' => 'None'
            ];
        }
    }

    /**
     * Helper function to create consistent status structure
     */
    public function getStatusStructure($status, $label, $color, $description = null)
    {
        return [
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'description' => $description
        ];
    }
}
