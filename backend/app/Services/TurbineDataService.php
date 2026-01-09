<?php

namespace App\Services;

use App\Models\ScadaReading;
use App\Models\Threshold;
use Illuminate\Support\Facades\Cache;

class TurbineDataService
{
    /**
     * Get threshold from database with caching
     */
    private function getThreshold(string $componentName)
    {
        return Threshold::where('component_name', $componentName)->first();
    }

    /**
     * Get gearbox oil pressure status
     */
    public function getGearboxPressureStatus($pressure, $turbineId)
    {
        $scadaData = ScadaReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if ($scadaData && $scadaData->status_code !== 100) {
            return 'Turbine is not running';
        }

        $threshold = $this->getThreshold('gearbox_oil_pressure');

        if (!$threshold) {
            return $this->getGearboxPressureStatusFallback($pressure);
        }

        // ✅ USE MODEL METHOD - handles min/max correctly
        $status = $threshold->getStatus($pressure);

        return match($status) {
            'normal' => [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green',
                'description' => 'Adequate lubrication pressure'
            ],
            'warning' => [
                'status' => 'warning',
                'label' => 'Low Pressure',
                'color' => 'yellow',
                'description' => 'Monitor lubrication system'
            ],
            'critical' => [
                'status' => 'critical',
                'label' => 'Very Low Pressure',
                'color' => 'orange',
                'description' => 'Lubrication failure risk'
            ],
            'failed' => [
                'status' => 'failed',
                'label' => 'Critically Low',
                'color' => 'red',
                'description' => 'Lubrication system failure'
            ],
            default => [
                'status' => 'unknown',
                'label' => 'Unknown',
                'color' => 'gray',
                'description' => 'Unable to determine status'
            ]
        };
    }

    /**
     * Get hydraulic pressure status
     */
    public function getHydraulicPressureStatus($pressure)
    {
        $threshold = $this->getThreshold('hydraulic_pressure');

        if (!$threshold) {
            return $this->getHydraulicPressureStatusFallback($pressure);
        }

        // ✅ USE MODEL METHOD
        $status = $threshold->getStatus($pressure);

        return match($status) {
            'normal' => [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green',
                'description' => 'Optimal pitch system pressure'
            ],
            'warning' => [
                'status' => 'warning',
                'label' => 'Below Normal',
                'color' => 'yellow',
                'description' => 'Pitch response may be slower'
            ],
            'critical' => [
                'status' => 'critical',
                'label' => 'Low Pressure',
                'color' => 'orange',
                'description' => 'Pitch system compromised'
            ],
            'failed' => [
                'status' => 'failed',
                'label' => 'Pressure Critical',
                'color' => 'red',
                'description' => 'Pitch system failure - emergency shutdown required'
            ],
            default => [
                'status' => 'unknown',
                'label' => 'Unknown',
                'color' => 'gray',
                'description' => 'Unable to determine status'
            ]
        };
    }

    /**
     * Get vibration status based on ISO 10816 standard
     */
    public function getVibrationStatus($vibration_mms, $componentName = 'main_bearing_vibration_rms')
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

        $threshold = $this->getThreshold($componentName);

        if (!$threshold) {
            return $this->getVibrationStatusFallback($vibration_mms);
        }

        // ✅ USE MODEL METHOD
        $status = $threshold->getStatus($vibration_mms);

        return match($status) {
            'normal' => [
                'status' => 'normal',
                'label' => 'Good',
                'zone' => 'Zone A',
                'color' => 'green',
                'severity' => 'none',
                'description' => 'Acceptable for long-term operation'
            ],
            'warning' => [
                'status' => 'warning',
                'label' => 'Caution',
                'zone' => 'Zone B',
                'color' => 'yellow',
                'severity' => 'warning',
                'description' => 'Monitor closely - plan maintenance'
            ],
            'critical' => [
                'status' => 'critical',
                'label' => 'Critical',
                'zone' => 'Zone C',
                'color' => 'orange',
                'severity' => 'critical',
                'description' => 'Urgent maintenance required'
            ],
            'failed' => [
                'status' => 'failed',
                'label' => 'Damage Imminent',
                'zone' => 'Zone D',
                'color' => 'red',
                'severity' => 'failed',
                'description' => 'Component failure - immediate shutdown'
            ],
            default => [
                'status' => 'unknown',
                'label' => 'Unknown',
                'zone' => 'N/A',
                'color' => 'gray',
                'severity' => 'none'
            ]
        };
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

        $threshold = $this->getThreshold('acoustic_level');

        if (!$threshold) {
            return $this->getAcousticStatusFallback($db_level);
        }

        // ✅ USE MODEL METHOD
        $status = $threshold->getStatus($db_level);

        return match($status) {
            'normal' => [
                'status' => 'normal',
                'label' => 'Normal',
                'color' => 'green',
                'description' => 'Typical noise level'
            ],
            'warning' => [
                'status' => 'warning',
                'label' => 'Elevated',
                'color' => 'yellow',
                'description' => 'Higher than usual - investigate'
            ],
            'critical' => [
                'status' => 'critical',
                'label' => 'Excessive',
                'color' => 'red',
                'description' => 'Excessive noise - investigate immediately'
            ],
            default => [
                'status' => 'unknown',
                'label' => 'Unknown',
                'color' => 'gray'
            ]
        };
    }

    /**
     * Get overall vibration health assessment
     */
    public function getOverallVibrationStatus($vibration)
    {
        $components = [
            'main_bearing' => [
                'value' => $vibration->main_bearing_vibration_rms_mms,
                'name' => 'main_bearing_vibration_rms'
            ],
            'gearbox' => [
                'value' => max(
                    $vibration->gearbox_vibration_axial_mms ?? 0,
                    $vibration->gearbox_vibration_radial_mms ?? 0
                ),
                'name' => 'gearbox_vibration_axial'
            ],
            'generator' => [
                'value' => max(
                    $vibration->generator_vibration_de_mms ?? 0,
                    $vibration->generator_vibration_nde_mms ?? 0
                ),
                'name' => 'generator_vibration_de'
            ],
            'tower' => [
                'value' => max(
                    $vibration->tower_vibration_fa_mms ?? 0,
                    $vibration->tower_vibration_ss_mms ?? 0
                ),
                'name' => 'tower_vibration_fa'
            ],
        ];

        $criticalCount = 0;
        $warningCount = 0;

        foreach ($components as $component) {
            $threshold = $this->getThreshold($component['name']);
            if (!$threshold) continue;

            $status = $threshold->getStatus($component['value']);

            if ($status === 'critical' || $status === 'failed') {
                $criticalCount++;
            } elseif ($status === 'warning') {
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

        $threshold = $this->getThreshold('nacelle_temp');

        if (!$threshold) {
            return $this->getNacelleTemperatureStatusFallback($temp);
        }

        // ✅ USE MODEL METHOD
        $status = $threshold->getStatus($temp);

        return match($status) {
            'normal' => $this->getStatusStructure('normal', 'Normal', 'green', 'Acceptable nacelle temperature'),
            'warning' => $this->getStatusStructure('warning', 'Warm', 'yellow', 'Higher than typical'),
            'critical' => $this->getStatusStructure('critical', 'Hot', 'orange', 'Check cooling system'),
            'failed' => $this->getStatusStructure('failed', 'Very Hot', 'red', 'Excessive nacelle heat - shutdown risk'),
            default => $this->getStatusStructure('unknown', 'No Data', 'gray')
        };
    }

    /**
     * Get gearbox bearing temperature status
     */
    public function getGearboxBearingTempStatus($temp, $ambient, $loadFactor)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        $threshold = $this->getThreshold('gearbox_bearing_temp');

        if (!$threshold) {
            return $this->getGearboxBearingTempStatusFallback($temp, $ambient, $loadFactor);
        }

        // ✅ USE MODEL METHOD
        $status = $threshold->getStatus($temp);

        return match($status) {
            'normal' => $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range'),
            'warning' => $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Monitor - trending warm'),
            'critical' => $this->getStatusStructure('critical', 'High', 'orange', 'Urgent - plan maintenance'),
            'failed' => $this->getStatusStructure('failed', 'Very High', 'red', 'Critical overheating'),
            default => $this->getStatusStructure('unknown', 'No Data', 'gray')
        };
    }

    /**
     * Get gearbox oil temperature status
     */
    public function getGearboxOilTempStatus($temp)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        $threshold = $this->getThreshold('gearbox_oil_temp');

        if (!$threshold) {
            return $this->getGearboxOilTempStatusFallback($temp);
        }

        // ✅ USE MODEL METHOD
        $status = $threshold->getStatus($temp);

        return match($status) {
            'normal' => $this->getStatusStructure('normal', 'Normal', 'green', 'Optimal operating temperature'),
            'warning' => $this->getStatusStructure('warning', 'Warm', 'yellow', 'Approaching warning level'),
            'critical' => $this->getStatusStructure('critical', 'Very Hot', 'orange', 'Check cooling system'),
            'failed' => $this->getStatusStructure('failed', 'Critical', 'red', 'Oil degradation risk'),
            default => $this->getStatusStructure('unknown', 'No Data', 'gray')
        };
    }

    /**
     * Get generator temperature status
     */
    public function getGeneratorTemperatureStatus($statorTemp, $ambient, $loadFactor)
    {
        if ($statorTemp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        $threshold = $this->getThreshold('generator_stator_temp');

        if (!$threshold) {
            return $this->getGeneratorTemperatureStatusFallback($statorTemp, $ambient, $loadFactor);
        }

        // ✅ USE MODEL METHOD
        $status = $threshold->getStatus($statorTemp);

        $expectedTemp = $ambient + (55 * $loadFactor);
        $deviation = $statorTemp - $expectedTemp;

        return match($status) {
            'normal' => $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range'),
            'warning' => $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Above expected by ' . round($deviation) . '°C'),
            'critical' => $this->getStatusStructure('critical', 'High', 'orange', 'Critical - implement power curtailment'),
            'failed' => $this->getStatusStructure('failed', 'Failed', 'red', 'Component failure risk'),
            default => $this->getStatusStructure('unknown', 'No Data', 'gray')
        };
    }

    /**
     * Get main bearing temperature status
     */
    public function getMainBearingTempStatus($temp, $ambient, $loadFactor)
    {
        if ($temp === null) {
            return $this->getStatusStructure('unknown', 'No Data', 'gray');
        }

        $threshold = $this->getThreshold('main_bearing_temp');

        if (!$threshold) {
            return $this->getMainBearingTempStatusFallback($temp, $ambient, $loadFactor);
        }

        // ✅ USE MODEL METHOD
        $status = $threshold->getStatus($temp);

        return match($status) {
            'normal' => $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range'),
            'warning' => $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Monitor bearing condition'),
            'critical' => $this->getStatusStructure('critical', 'High', 'orange', 'Check lubrication system'),
            'failed' => $this->getStatusStructure('failed', 'Very High', 'red', 'Bearing damage risk'),
            default => $this->getStatusStructure('unknown', 'No Data', 'gray')
        };
    }

    /**
     * Get overall temperature assessment
     */
    public function getOverallTemperatureStatus($temperature, $loadFactor)
    {
        $criticalComponents = [];
        $warningComponents = [];

        $checks = [
            'Generator Stator' => ['value' => $temperature->generator_stator_temp_c, 'threshold' => 'generator_stator_temp'],
            'Gearbox Bearing' => ['value' => $temperature->gearbox_bearing_temp_c, 'threshold' => 'gearbox_bearing_temp'],
            'Gearbox Oil' => ['value' => $temperature->gearbox_oil_temp_c, 'threshold' => 'gearbox_oil_temp'],
            'Main Bearing' => ['value' => $temperature->main_bearing_temp_c, 'threshold' => 'main_bearing_temp'],
        ];

        foreach ($checks as $component => $data) {
            if ($data['value'] === null) continue;

            $threshold = $this->getThreshold($data['threshold']);
            if (!$threshold) continue;

            $status = $threshold->getStatus($data['value']);

            if ($status === 'critical' || $status === 'failed') {
                $criticalComponents[] = $component;
            } elseif ($status === 'warning') {
                $warningComponents[] = $component;
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

    // ========== FALLBACK METHODS (unchanged) ==========

    private function getGearboxPressureStatusFallback($pressure)
    {
        if ($pressure >= 3.5) {
            return ['status' => 'critical', 'label' => 'Very High Pressure', 'color' => 'orange', 'description' => 'Lubrication failure risk'];
        } else if ($pressure >= 3.1) {
            return ['status' => 'warning', 'label' => 'High Pressure', 'color' => 'yellow', 'description' => 'Monitor lubrication system'];
        } else if ($pressure >= 2.3) {
            return ['status' => 'normal', 'label' => 'Normal', 'color' => 'green', 'description' => 'Adequate lubrication pressure'];
        } elseif ($pressure >= 2.0) {
            return ['status' => 'warning', 'label' => 'Low Pressure', 'color' => 'yellow', 'description' => 'Monitor lubrication system'];
        } elseif ($pressure >= 1.8) {
            return ['status' => 'critical', 'label' => 'Very Low Pressure', 'color' => 'orange', 'description' => 'Lubrication failure risk'];
        } else {
            return ['status' => 'failed', 'label' => 'Critically Low', 'color' => 'red', 'description' => 'Lubrication system failure'];
        }
    }

    private function getHydraulicPressureStatusFallback($pressure)
    {
        if ($pressure >= 175) {
            return ['status' => 'critical', 'label' => 'Low Pressure', 'color' => 'orange', 'description' => 'Pitch system compromised'];
        } elseif ($pressure >= 165) {
            return ['status' => 'warning', 'label' => 'Above Normal', 'color' => 'yellow', 'description' => 'Pitch response may be slower'];
        } elseif ($pressure >= 155) {
            return ['status' => 'normal', 'label' => 'Normal', 'color' => 'green', 'description' => 'Optimal pitch system pressure'];
        } elseif ($pressure >= 150) {
            return ['status' => 'warning', 'label' => 'Below Normal', 'color' => 'yellow', 'description' => 'Pitch response may be slower'];
        } elseif ($pressure >= 140) {
            return ['status' => 'critical', 'label' => 'Low Pressure', 'color' => 'orange', 'description' => 'Pitch system compromised'];
        } else {
            return ['status' => 'failed', 'label' => '   Critical', 'color' => 'red', 'description' => 'Pitch system failure'];
        }
    }

    private function getVibrationStatusFallback($vibration_mms)
    {
        if ($vibration_mms <= 4.5) {
            return ['status' => 'normal', 'label' => 'Good', 'zone' => 'Zone A', 'color' => 'green', 'severity' => 'none'];
        } elseif ($vibration_mms <= 7.1) {
            return ['status' => 'warning', 'label' => 'Caution', 'zone' => 'Zone B', 'color' => 'yellow', 'severity' => 'warning'];
        } elseif ($vibration_mms <= 11.2) {
            return ['status' => 'critical', 'label' => 'Critical', 'zone' => 'Zone C', 'color' => 'orange', 'severity' => 'critical'];
        } else {
            return ['status' => 'failed', 'label' => 'Damage Imminent', 'zone' => 'Zone D', 'color' => 'red', 'severity' => 'failed'];
        }
    }

    private function getAcousticStatusFallback($db_level)
    {
        if ($db_level < 102) {
            return ['status' => 'normal', 'label' => 'Normal', 'color' => 'green', 'description' => 'Typical noise level'];
        } elseif ($db_level <= 105) {
            return ['status' => 'warning', 'label' => 'Elevated', 'color' => 'yellow', 'description' => 'Higher than usual'];
        } else {
            return ['status' => 'failed', 'label' => 'Excessive', 'color' => 'red', 'description' => 'Excessive noise'];
        }
    }

    private function getNacelleTemperatureStatusFallback($temp)
    {
        if ($temp < 50) return $this->getStatusStructure('normal', 'Normal', 'green', 'Acceptable nacelle temperature');
        elseif ($temp < 70) return $this->getStatusStructure('warning', 'Warm', 'yellow', 'Higher than typical');
        elseif ($temp < 80) return $this->getStatusStructure('critical', 'Hot', 'orange', 'Check cooling system');
        else return $this->getStatusStructure('failed', 'Very Hot', 'red', 'Excessive nacelle heat');
    }

    private function getGearboxBearingTempStatusFallback($temp, $ambient, $loadFactor)
    {
        if ($temp < 80) return $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range');
        elseif ($temp < 90) return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Monitor - trending warm');
        elseif ($temp < 100) return $this->getStatusStructure('critical', 'High', 'orange', 'Urgent - plan maintenance');
        else return $this->getStatusStructure('failed', 'Very High', 'red', 'Critical overheating');
    }

    private function getGearboxOilTempStatusFallback($temp)
    {
        if ($temp < 65) return $this->getStatusStructure('normal', 'Normal', 'green', 'Optimal operating temperature');
        elseif ($temp < 75) return $this->getStatusStructure('warning', 'Warm', 'yellow', 'Approaching warning level');
        elseif ($temp < 85) return $this->getStatusStructure('critical', 'Very Hot', 'orange', 'Check cooling system');
        else return $this->getStatusStructure('failed', 'Critical', 'red', 'Oil degradation risk');
    }

    private function getGeneratorTemperatureStatusFallback($statorTemp, $ambient, $loadFactor)
    {
        if ($statorTemp < 120) return $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range');
        elseif ($statorTemp < 140) return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Above expected');
        elseif ($statorTemp < 155) return $this->getStatusStructure('critical', 'High', 'orange', 'Critical - implement power curtailment');
        else return $this->getStatusStructure('failed', 'Failed', 'red', 'Component failure risk');
    }

    private function getMainBearingTempStatusFallback($temp, $ambient, $loadFactor)
    {
        if ($temp < 80) return $this->getStatusStructure('normal', 'Normal', 'green', 'Within expected range');
        elseif ($temp < 90) return $this->getStatusStructure('warning', 'Elevated', 'yellow', 'Monitor bearing condition');
        elseif ($temp < 100) return $this->getStatusStructure('critical', 'High', 'orange', 'Check lubrication system');
        else return $this->getStatusStructure('failed', 'Very High', 'red', 'Bearing damage risk');
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
