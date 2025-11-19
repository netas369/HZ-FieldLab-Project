<?php

namespace App\Services;

use App\Models\ScadaReading;

class TurbineDataService
{

    /**
     * Get gearbox oil pressure status
     * Documentation: 2.3-2.5 bar = Normal, 2.0-2.3 = Warning, 1.8-2.0 = Critical, <1.8 = Failed
     */
    public function getGearboxPressureStatus($pressure, $turbineId)
    {
        $scadaData = ScadaReading::where('turbine_id', $turbineId)->latest('reading_timestamp')->first();
        if ($scadaData && $scadaData->status_code !== 100) {
            return 'Turbine is not running';
        }

        if ($pressure >= 2.3) {
            return [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green',
                'description' => 'Adequate lubrication pressure'
            ];
        } elseif ($pressure >= 2.0 && $pressure < 2.3) {
            return [
                'status' => 'warning',
                'label' => 'Low Pressure',
                'color' => 'yellow',
                'description' => 'Monitor lubrication system'
            ];
        } elseif ($pressure >= 1.8 && $pressure < 2.0) {
            return [
                'status' => 'critical',
                'label' => 'Very Low Pressure',
                'color' => 'orange',
                'description' => 'Lubrication failure risk'
            ];
        } else {
            return [
                'status' => 'failed',
                'label' => 'Critically Low',
                'color' => 'red',
                'description' => 'Lubrication system failure'
            ];
        }
    }

    /**
     * Get hydraulic pressure status
     * Documentation: 160-180 bar = Normal, 150-160 = Warning, 140-150 = Critical, <140 = Failed
     */
    public function getHydraulicPressureStatus($pressure)
    {
        if ($pressure >= 160 && $pressure <= 180) {
            return [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green',
                'description' => 'Optimal pitch system pressure'
            ];
        } elseif ($pressure >= 150 && $pressure < 160) {
            return [
                'status' => 'warning',
                'label' => 'Below Normal',
                'color' => 'yellow',
                'description' => 'Pitch response may be slower'
            ];
        } elseif ($pressure >= 140 && $pressure < 150) {
            return [
                'status' => 'critical',
                'label' => 'Low Pressure',
                'color' => 'orange',
                'description' => 'Pitch system compromised'
            ];
        } else {
            return [
                'status' => 'failed',
                'label' => 'Pressure Critical',
                'color' => 'red',
                'description' => 'Pitch system failure - emergency shutdown required'
            ];
        }
    }

    /**
     * Get vibration status based on ISO 10816 standard
     * Documentation: <4.5 = Normal, 4.5-7.1 = Warning, 7.1-11.2 = Critical, >11.2 = Failed
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

        // ISO 10816 Zones - Aligned with documentation thresholds
        if ($vibration_mms <= 4.5) {
            return [
                'status' => 'normal',
                'label' => 'Good',
                'zone' => 'Zone A',
                'color' => 'green',
                'severity' => 'none',
                'description' => 'Acceptable for long-term operation'
            ];
        } elseif ($vibration_mms > 4.5 && $vibration_mms <= 7.1) {
            return [
                'status' => 'warning',
                'label' => 'Caution',
                'zone' => 'Zone B',
                'color' => 'yellow',
                'severity' => 'warning',
                'description' => 'Monitor closely - plan maintenance'
            ];
        } elseif ($vibration_mms > 7.1 && $vibration_mms <= 11.2) {
            return [
                'status' => 'critical',
                'label' => 'Critical',
                'zone' => 'Zone C',
                'color' => 'orange',
                'severity' => 'critical',
                'description' => 'Urgent maintenance required'
            ];
        } else {
            return [
                'status' => 'failed',
                'label' => 'Damage Imminent',
                'zone' => 'Zone D',
                'color' => 'red',
                'severity' => 'failed',
                'description' => 'Component failure - immediate shutdown'
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
                'balanced' => null,
                'imbalance' => 0
            ];
        }

        $maxVibration = max($blade1, $blade2, $blade3);
        $avgVibration = ($blade1 + $blade2 + $blade3) / 3;

        // Check if blades are balanced (difference < 30% from average)
        $imbalance = $avgVibration > 0 ? abs($maxVibration - $avgVibration) / $avgVibration : 0;
        $isBalanced = $imbalance < 0.3;

        // Determine status based on imbalance
        if ($imbalance < 0.3) {
            $status = 'normal';
            $label = 'Balanced';
            $color = 'green';
        } else {
            $status = 'warning';
            $label = 'Imbalanced';
            $color = 'yellow';
        }

        return [
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'balanced' => $isBalanced,
            'imbalance' => $imbalance,
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
     * Documentation: <102 dB = Normal, 102-105 = Warning, >105 = Failed
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

        if ($db_level < 102) {
            return [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green',
                'description' => 'Typical noise level'
            ];
        } elseif ($db_level >= 102 && $db_level <= 105) {
            return [
                'status' => 'warning',
                'label' => 'Elevated',
                'color' => 'yellow',
                'description' => 'Higher than usual - investigate'
            ];
        } else {
            return [
                'status' => 'failed',
                'label' => 'Excessive',
                'color' => 'red',
                'description' => 'Excessive noise - investigate immediately'
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
     * Documentation: <50°C = Normal, 50-70°C = Warning, 70-80°C = Critical, >80°C = Failed
     */
    public function getNacelleTemperatureStatus($temp)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        if ($temp < 50) {
            return $this->getStatusStructure('normal', 'Normal', 'green', 'Acceptable nacelle temperature');
        } elseif ($temp >= 50 && $temp < 70) {
            return $this->getStatusStructure('warning', 'Warm', 'yellow', 'Higher than typical');
        } elseif ($temp >= 70 && $temp < 80) {
            return $this->getStatusStructure('critical', 'Hot', 'orange', 'Check cooling system');
        } else {
            return $this->getStatusStructure('failed', 'Very Hot', 'red', 'Excessive nacelle heat - shutdown risk');
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

        $expectedTemp = $ambient + (30 * $loadFactor);
        $deviation = $temp - $expectedTemp;

        if ($deviation < 10) {
            return $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range');
        } elseif ($deviation >= 10 && $deviation < 20) {
            return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Monitor - trending warm');
        } elseif ($deviation >= 20 && $deviation < 30) {
            return $this->getStatusStructure('critical', 'High', 'orange', 'Urgent - plan maintenance');
        } else {
            return $this->getStatusStructure('failed', 'Very High', 'red', 'Critical overheating');
        }
    }

    /**
     * Get gearbox oil temperature status
     * Documentation: <70°C = Normal, 70-75°C = Warning, 75-85°C = Critical, >85°C = Failed
     */
    public function getGearboxOilTempStatus($temp)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        if ($temp < 70) {
            return $this->getStatusStructure('normal', 'Normal', 'green', 'Optimal operating temperature');
        } elseif ($temp >= 70 && $temp < 75) {
            return $this->getStatusStructure('warning', 'Warm', 'yellow', 'Higher than optimal');
        } elseif ($temp >= 75 && $temp < 85) {
            return $this->getStatusStructure('critical', 'Hot', 'orange', 'Check cooling system');
        } else {
            return $this->getStatusStructure('failed', 'Very Hot', 'red', 'Oil degradation risk');
        }
    }

    /**
     * Get generator temperature status
     * Documentation: Expected = Ambient + (55 × load_factor)
     * Deviation: +10-20°C = Warning, +20-30°C = Critical, >30°C = Failed
     */
    public function getGeneratorTemperatureStatus($statorTemp, $ambient, $loadFactor)
    {
        if ($statorTemp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        $expectedTemp = $ambient + (55 * $loadFactor);
        $deviation = $statorTemp - $expectedTemp;

        if ($deviation < 10) {
            return $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range');
        } elseif ($deviation >= 10 && $deviation < 20) {
            return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Above expected by ' . round($deviation) . '°C');
        } elseif ($deviation >= 20 && $deviation < 30) {
            return $this->getStatusStructure('critical', 'High', 'orange', 'Critical - implement power curtailment');
        } else {
            return $this->getStatusStructure('failed', 'Failed', 'red', 'Component failure risk');
        }
    }

    /**
     * Get main bearing temperature status
     * Documentation: Expected = Ambient + (30 × load_factor)
     * Deviation: +10-20°C = Warning, +20-30°C = Critical, >30°C = Failed
     */
    public function getMainBearingTempStatus($temp, $ambient, $loadFactor)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        $expectedTemp = $ambient + (30 * $loadFactor);
        $deviation = $temp - $expectedTemp;

        if ($deviation < 10) {
            return $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range');
        } elseif ($deviation >= 10 && $deviation < 20) {
            return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Monitor bearing condition');
        } elseif ($deviation >= 20 && $deviation < 30) {
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

        $checks = [
            'Generator Stator' => $temperature->generator_stator_temp_c,
            'Gearbox Bearing' => $temperature->gearbox_bearing_temp_c,
            'Gearbox Oil' => $temperature->gearbox_oil_temp_c,
            'Main Bearing' => $temperature->main_bearing_temp_c,
        ];

        foreach ($checks as $component => $temp) {
            if ($temp === null) continue;

            if ($component === 'Generator Stator') {
                if ($temp >= 115) $criticalComponents[] = $component;
                elseif ($temp >= 100) $warningComponents[] = $component;
            } elseif ($component === 'Gearbox Oil') {
                if ($temp >= 85) $criticalComponents[] = $component;
                elseif ($temp >= 70) $warningComponents[] = $component;
            } else {
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
    private function getStatusStructure($status, $label, $color, $description = null)
    {
        return [
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'description' => $description
        ];
    }

    // Keep existing utility methods unchanged
    public function getStatusSeverity($statusCode)
    {
        $severity = [
            100 => 'normal',
            200 => 'idle',
            300 => 'maintenance',
            400 => 'critical',
            500 => 'external',
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

    public function getAlarmDescription($alarmCode)
    {
        if (!$alarmCode || $alarmCode == 0) {
            return null;
        }

        $alarms = [
            1001 => 'Main Bearing Wear Warning',
            1002 => 'Gearbox Oil Quality Warning',
            1003 => 'Blade Imbalance Detected',
            1004 => 'Generator Overheating Warning',
            1005 => 'Yaw System Response Degraded',
            2001 => 'Main Bearing Critical - Urgent Maintenance Required',
            2002 => 'Gearbox Critical - Oil Change Required Urgently',
            2003 => 'Blade System Critical',
            2004 => 'Generator Critical - Urgent Service Required',
            3001 => 'Main Bearing Failed - Emergency Shutdown',
            3002 => 'Gearbox Failed - Component Replacement Required',
            3003 => 'Pitch System Failed - Emergency Shutdown',
            3004 => 'Generator Failed - Component Replacement Required',
            5001 => 'Grid Fault - External Issue',
        ];

        return $alarms[$alarmCode] ?? "Unknown Alarm Code ($alarmCode)";
    }

    public function getAlarmSeverity($alarmCode)
    {
        if (!$alarmCode || $alarmCode == 0) {
            return 'none';
        }

        if ($alarmCode >= 1001 && $alarmCode <= 1999) {
            return 'warning';
        } elseif ($alarmCode >= 2001 && $alarmCode <= 2999) {
            return 'critical';
        } elseif ($alarmCode >= 3001 && $alarmCode <= 3999) {
            return 'failed';
        } elseif ($alarmCode >= 5001 && $alarmCode <= 5999) {
            return 'external';
        }

        return 'unknown';
    }
}
