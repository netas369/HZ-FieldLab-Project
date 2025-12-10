<?php

namespace App\Services;

use App\Models\Alarm;
use App\Models\VibrationReading;
use App\Models\TemperatureReading;
use App\Models\HydraulicReading;
use App\Models\ScadaReading;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComponentHealthService
{
    /**
     * Calculate health for all components of a turbine
     */
    public function calculateAllComponentsHealth(int $turbineId, int $daysBack = 365): array
    {
        return [
            'turbine_id' => $turbineId,
            'calculation_timestamp' => now(),
            'period_days' => $daysBack,
            'components' => [
                'main_bearing' => $this->calculateMainBearingHealth($turbineId, $daysBack),
                'gearbox' => $this->calculateGearboxHealth($turbineId, $daysBack),
                'generator' => $this->calculateGeneratorHealth($turbineId, $daysBack),
                'blade_1' => $this->calculateBladeHealth($turbineId, 1, $daysBack),
                'blade_2' => $this->calculateBladeHealth($turbineId, 2, $daysBack),
                'blade_3' => $this->calculateBladeHealth($turbineId, 3, $daysBack),
                'hydraulic_system' => $this->calculateHydraulicHealth($turbineId, $daysBack),
                'tower' => $this->calculateTowerHealth($turbineId, $daysBack),
            ],
            'overall_health' => $this->calculateOverallHealth($turbineId, $daysBack),
        ];
    }

    /**
     * ============================================
     * MAIN BEARING HEALTH
     * ============================================
     */
    public function calculateMainBearingHealth(int $turbineId, int $daysBack = 365): array
    {
        // Get current readings
        $current = $this->getCurrentMainBearingData($turbineId);

        // Get historical readings (from N days ago)
        $historical = $this->getHistoricalMainBearingData($turbineId, $daysBack);

        // Calculate alarm penalty
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, 'main_bearing', $daysBack);

        if (!$current) {
            return $this->createEmptyHealthData('main_bearing');
        }

        // Calculate penalties
        $vibrationPenalty = $this->calculateVibrationPenalty($current['vibration_rms'], 7.1, 40);
        $tempPenalty = $this->calculateTemperaturePenalty($current['temperature'], 65, 25, 30);

        // Calculate current health
        $currentHealth = 100 - min(100, $vibrationPenalty + $tempPenalty + $alarmPenalty);
        $currentHealth = max(0, $currentHealth);

        // Calculate historical health (if data exists)
        $historicalHealth = null;
        $deterioration = null;
        $deteriorationRate = null;

        if ($historical) {
            $histVibrationPenalty = $this->calculateVibrationPenalty($historical['vibration_rms'], 7.1, 40);
            $histTempPenalty = $this->calculateTemperaturePenalty($historical['temperature'], 65, 25, 30);

            $historicalHealth = 100 - min(100, $histVibrationPenalty + $histTempPenalty);
            $historicalHealth = max(0, $historicalHealth);

            $deterioration = $historicalHealth - $currentHealth;
            $deteriorationRate = $deterioration / $daysBack; // points per day
        }

        return [
            'component' => 'main_bearing',
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration_rms' => $current['vibration_rms'],
                'temperature' => $current['temperature'],
                'timestamp' => $current['timestamp'],
            ],
            'penalties' => [
                'vibration' => round($vibrationPenalty, 1),
                'temperature' => round($tempPenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($vibrationPenalty + $tempPenalty + $alarmPenalty, 1),
            ],
            'trend_analysis' => [
                'health_' . $daysBack . '_days_ago' => $historicalHealth ? round($historicalHealth, 1) : null,
                'deterioration_points' => $deterioration ? round($deterioration, 1) : null,
                'deterioration_rate_per_day' => $deteriorationRate ? round($deteriorationRate, 3) : null,
                'deterioration_rate_per_year' => $deteriorationRate ? round($deteriorationRate * 365, 1) : null,
                'deterioration_level' => $this->getDeteriorationLevel($deterioration, $daysBack),
                'days_to_critical' => $this->calculateDaysToCritical($currentHealth, $deteriorationRate),
            ],
        ];
    }

    /**
     * ============================================
     * GEARBOX HEALTH
     * ============================================
     */
    public function calculateGearboxHealth(int $turbineId, int $daysBack = 365): array
    {
        $current = $this->getCurrentGearboxData($turbineId);
        $historical = $this->getHistoricalGearboxData($turbineId, $daysBack);
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, 'gearbox', $daysBack);

        if (!$current) {
            return $this->createEmptyHealthData('gearbox');
        }

        // Calculate penalties (Gearbox has axial + radial vibration)
        $vibrationPenaltyAxial = $this->calculateVibrationPenalty($current['vibration_axial'], 7.1, 20);
        $vibrationPenaltyRadial = $this->calculateVibrationPenalty($current['vibration_radial'], 7.1, 20);
        $tempBearingPenalty = $this->calculateTemperaturePenalty($current['bearing_temp'], 70, 20, 15);
        $tempOilPenalty = $this->calculateTemperaturePenalty($current['oil_temp'], 75, 25, 15);
        $oilPressurePenalty = $this->calculateOilPressurePenalty($current['oil_pressure'], 2.5, 20);

        $currentHealth = 100 - min(100,
                $vibrationPenaltyAxial +
                $vibrationPenaltyRadial +
                $tempBearingPenalty +
                $tempOilPenalty +
                $oilPressurePenalty +
                $alarmPenalty
            );
        $currentHealth = max(0, $currentHealth);

        // Historical calculation
        $historicalHealth = null;
        $deterioration = null;
        if ($historical) {
            $histVibAxial = $this->calculateVibrationPenalty($historical['vibration_axial'], 7.1, 20);
            $histVibRadial = $this->calculateVibrationPenalty($historical['vibration_radial'], 7.1, 20);
            $histTempBearing = $this->calculateTemperaturePenalty($historical['bearing_temp'], 70, 20, 15);
            $histTempOil = $this->calculateTemperaturePenalty($historical['oil_temp'], 75, 25, 15);
            $histOilPress = $this->calculateOilPressurePenalty($historical['oil_pressure'], 2.5, 20);

            $historicalHealth = 100 - min(100,
                    $histVibAxial + $histVibRadial + $histTempBearing + $histTempOil + $histOilPress
                );
            $historicalHealth = max(0, $historicalHealth);
            $deterioration = $historicalHealth - $currentHealth;
        }

        $deteriorationRate = $deterioration ? ($deterioration / $daysBack) : null;

        return [
            'component' => 'gearbox',
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration_axial' => $current['vibration_axial'],
                'vibration_radial' => $current['vibration_radial'],
                'bearing_temp' => $current['bearing_temp'],
                'oil_temp' => $current['oil_temp'],
                'oil_pressure' => $current['oil_pressure'],
                'timestamp' => $current['timestamp'],
            ],
            'penalties' => [
                'vibration_axial' => round($vibrationPenaltyAxial, 1),
                'vibration_radial' => round($vibrationPenaltyRadial, 1),
                'bearing_temperature' => round($tempBearingPenalty, 1),
                'oil_temperature' => round($tempOilPenalty, 1),
                'oil_pressure' => round($oilPressurePenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($vibrationPenaltyAxial + $vibrationPenaltyRadial + $tempBearingPenalty + $tempOilPenalty + $oilPressurePenalty + $alarmPenalty, 1),
            ],
            'trend_analysis' => [
                'health_' . $daysBack . '_days_ago' => $historicalHealth ? round($historicalHealth, 1) : null,
                'deterioration_points' => $deterioration ? round($deterioration, 1) : null,
                'deterioration_rate_per_day' => $deteriorationRate ? round($deteriorationRate, 3) : null,
                'deterioration_rate_per_year' => $deteriorationRate ? round($deteriorationRate * 365, 1) : null,
                'deterioration_level' => $this->getDeteriorationLevel($deterioration, $daysBack),
                'days_to_critical' => $this->calculateDaysToCritical($currentHealth, $deteriorationRate),
            ],
        ];
    }

    /**
     * ============================================
     * GENERATOR HEALTH
     * ============================================
     */
    public function calculateGeneratorHealth(int $turbineId, int $daysBack = 365): array
    {
        $current = $this->getCurrentGeneratorData($turbineId);
        $historical = $this->getHistoricalGeneratorData($turbineId, $daysBack);
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, 'generator', $daysBack);

        if (!$current) {
            return $this->createEmptyHealthData('generator');
        }

        // Penalties: DE + NDE vibration, bearing temps, stator temp
        $vibrationDE = $this->calculateVibrationPenalty($current['vibration_de'], 7.1, 15);
        $vibrationNDE = $this->calculateVibrationPenalty($current['vibration_nde'], 7.1, 15);
        $bearing1TempPenalty = $this->calculateTemperaturePenalty($current['bearing1_temp'], 75, 20, 10);
        $bearing2TempPenalty = $this->calculateTemperaturePenalty($current['bearing2_temp'], 75, 20, 10);
        $statorTempPenalty = $this->calculateTemperaturePenalty($current['stator_temp'], 110, 30, 10);

        $currentHealth = 100 - min(100,
                $vibrationDE +
                $vibrationNDE +
                $bearing1TempPenalty +
                $bearing2TempPenalty +
                $statorTempPenalty +
                $alarmPenalty
            );
        $currentHealth = max(0, $currentHealth);

        // Historical
        $historicalHealth = null;
        $deterioration = null;
        if ($historical) {
            $histVibDE = $this->calculateVibrationPenalty($historical['vibration_de'], 7.1, 15);
            $histVibNDE = $this->calculateVibrationPenalty($historical['vibration_nde'], 7.1, 15);
            $histBearing1 = $this->calculateTemperaturePenalty($historical['bearing1_temp'], 75, 20, 10);
            $histBearing2 = $this->calculateTemperaturePenalty($historical['bearing2_temp'], 75, 20, 10);
            $histStator = $this->calculateTemperaturePenalty($historical['stator_temp'], 110, 30, 10);

            $historicalHealth = 100 - min(100,
                    $histVibDE + $histVibNDE + $histBearing1 + $histBearing2 + $histStator
                );
            $historicalHealth = max(0, $historicalHealth);
            $deterioration = $historicalHealth - $currentHealth;
        }

        $deteriorationRate = $deterioration ? ($deterioration / $daysBack) : null;

        return [
            'component' => 'generator',
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration_de' => $current['vibration_de'],
                'vibration_nde' => $current['vibration_nde'],
                'bearing1_temp' => $current['bearing1_temp'],
                'bearing2_temp' => $current['bearing2_temp'],
                'stator_temp' => $current['stator_temp'],
                'timestamp' => $current['timestamp'],
            ],
            'penalties' => [
                'vibration_de' => round($vibrationDE, 1),
                'vibration_nde' => round($vibrationNDE, 1),
                'bearing1_temperature' => round($bearing1TempPenalty, 1),
                'bearing2_temperature' => round($bearing2TempPenalty, 1),
                'stator_temperature' => round($statorTempPenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($vibrationDE + $vibrationNDE + $bearing1TempPenalty + $bearing2TempPenalty + $statorTempPenalty + $alarmPenalty, 1),
            ],
            'trend_analysis' => [
                'health_' . $daysBack . '_days_ago' => $historicalHealth ? round($historicalHealth, 1) : null,
                'deterioration_points' => $deterioration ? round($deterioration, 1) : null,
                'deterioration_rate_per_day' => $deteriorationRate ? round($deteriorationRate, 3) : null,
                'deterioration_rate_per_year' => $deteriorationRate ? round($deteriorationRate * 365, 1) : null,
                'deterioration_level' => $this->getDeteriorationLevel($deterioration, $daysBack),
                'days_to_critical' => $this->calculateDaysToCritical($currentHealth, $deteriorationRate),
            ],
        ];
    }

    /**
     * ============================================
     * BLADE HEALTH (Individual)
     * ============================================
     */
    public function calculateBladeHealth(int $turbineId, int $bladeNumber, int $daysBack = 365): array
    {
        $current = $this->getCurrentBladeData($turbineId, $bladeNumber);
        $historical = $this->getHistoricalBladeData($turbineId, $bladeNumber, $daysBack);
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, "blade{$bladeNumber}", $daysBack);

        if (!$current) {
            return $this->createEmptyHealthData("blade_{$bladeNumber}");
        }

        // Blade vibration threshold is higher than bearings (up to 10 mm/s)
        $vibrationPenalty = $this->calculateVibrationPenalty($current['vibration'], 10.0, 50);

        // Check blade balance (imbalance penalty)
        $imbalancePenalty = $this->calculateBladeImbalancePenalty($turbineId, $bladeNumber);

        $currentHealth = 100 - min(100, $vibrationPenalty + $imbalancePenalty + $alarmPenalty);
        $currentHealth = max(0, $currentHealth);

        // Historical
        $historicalHealth = null;
        $deterioration = null;
        if ($historical) {
            $histVibration = $this->calculateVibrationPenalty($historical['vibration'], 10.0, 50);
            $historicalHealth = 100 - min(100, $histVibration);
            $historicalHealth = max(0, $historicalHealth);
            $deterioration = $historicalHealth - $currentHealth;
        }

        $deteriorationRate = $deterioration ? ($deterioration / $daysBack) : null;

        return [
            'component' => "blade_{$bladeNumber}",
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration' => $current['vibration'],
                'timestamp' => $current['timestamp'],
            ],
            'penalties' => [
                'vibration' => round($vibrationPenalty, 1),
                'imbalance' => round($imbalancePenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($vibrationPenalty + $imbalancePenalty + $alarmPenalty, 1),
            ],
            'trend_analysis' => [
                'health_' . $daysBack . '_days_ago' => $historicalHealth ? round($historicalHealth, 1) : null,
                'deterioration_points' => $deterioration ? round($deterioration, 1) : null,
                'deterioration_rate_per_day' => $deteriorationRate ? round($deteriorationRate, 3) : null,
                'deterioration_rate_per_year' => $deteriorationRate ? round($deteriorationRate * 365, 1) : null,
                'deterioration_level' => $this->getDeteriorationLevel($deterioration, $daysBack),
                'days_to_critical' => $this->calculateDaysToCritical($currentHealth, $deteriorationRate),
            ],
        ];
    }

    /**
     * ============================================
     * HYDRAULIC SYSTEM HEALTH
     * ============================================
     */
    public function calculateHydraulicHealth(int $turbineId, int $daysBack = 365): array
    {
        $current = $this->getCurrentHydraulicData($turbineId);
        $historical = $this->getHistoricalHydraulicData($turbineId, $daysBack);
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, 'hydraulic', $daysBack);

        if (!$current) {
            return $this->createEmptyHealthData('hydraulic_system');
        }

        // Hydraulic pressure penalty (normal: 180 bar, critical: <140 bar)
        $pressurePenalty = $this->calculateHydraulicPressurePenalty($current['pressure'], 180, 50);

        $currentHealth = 100 - min(100, $pressurePenalty + $alarmPenalty);
        $currentHealth = max(0, $currentHealth);

        // Historical
        $historicalHealth = null;
        $deterioration = null;
        if ($historical) {
            $histPressure = $this->calculateHydraulicPressurePenalty($historical['pressure'], 180, 50);
            $historicalHealth = 100 - min(100, $histPressure);
            $historicalHealth = max(0, $historicalHealth);
            $deterioration = $historicalHealth - $currentHealth;
        }

        $deteriorationRate = $deterioration ? ($deterioration / $daysBack) : null;

        return [
            'component' => 'hydraulic_system',
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'pressure' => $current['pressure'],
                'timestamp' => $current['timestamp'],
            ],
            'penalties' => [
                'pressure' => round($pressurePenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($pressurePenalty + $alarmPenalty, 1),
            ],
            'trend_analysis' => [
                'health_' . $daysBack . '_days_ago' => $historicalHealth ? round($historicalHealth, 1) : null,
                'deterioration_points' => $deterioration ? round($deterioration, 1) : null,
                'deterioration_rate_per_day' => $deteriorationRate ? round($deteriorationRate, 3) : null,
                'deterioration_rate_per_year' => $deteriorationRate ? round($deteriorationRate * 365, 1) : null,
                'deterioration_level' => $this->getDeteriorationLevel($deterioration, $daysBack),
                'days_to_critical' => $this->calculateDaysToCritical($currentHealth, $deteriorationRate),
            ],
        ];
    }

    /**
     * ============================================
     * TOWER HEALTH
     * ============================================
     */
    public function calculateTowerHealth(int $turbineId, int $daysBack = 365): array
    {
        $current = $this->getCurrentTowerData($turbineId);
        $historical = $this->getHistoricalTowerData($turbineId, $daysBack);
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, 'tower', $daysBack);

        if (!$current) {
            return $this->createEmptyHealthData('tower');
        }

        // Tower vibration (higher threshold: 15 mm/s)
        $vibrationFA = $this->calculateVibrationPenalty($current['vibration_fa'], 15.0, 25);
        $vibrationSS = $this->calculateVibrationPenalty($current['vibration_ss'], 15.0, 25);

        $currentHealth = 100 - min(100, $vibrationFA + $vibrationSS + $alarmPenalty);
        $currentHealth = max(0, $currentHealth);

        // Historical
        $historicalHealth = null;
        $deterioration = null;
        if ($historical) {
            $histVibFA = $this->calculateVibrationPenalty($historical['vibration_fa'], 15.0, 25);
            $histVibSS = $this->calculateVibrationPenalty($historical['vibration_ss'], 15.0, 25);
            $historicalHealth = 100 - min(100, $histVibFA + $histVibSS);
            $historicalHealth = max(0, $historicalHealth);
            $deterioration = $historicalHealth - $currentHealth;
        }

        $deteriorationRate = $deterioration ? ($deterioration / $daysBack) : null;

        return [
            'component' => 'tower',
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration_fa' => $current['vibration_fa'],
                'vibration_ss' => $current['vibration_ss'],
                'timestamp' => $current['timestamp'],
            ],
            'penalties' => [
                'vibration_fa' => round($vibrationFA, 1),
                'vibration_ss' => round($vibrationSS, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($vibrationFA + $vibrationSS + $alarmPenalty, 1),
            ],
            'trend_analysis' => [
                'health_' . $daysBack . '_days_ago' => $historicalHealth ? round($historicalHealth, 1) : null,
                'deterioration_points' => $deterioration ? round($deterioration, 1) : null,
                'deterioration_rate_per_day' => $deteriorationRate ? round($deteriorationRate, 3) : null,
                'deterioration_rate_per_year' => $deteriorationRate ? round($deteriorationRate * 365, 1) : null,
                'deterioration_level' => $this->getDeteriorationLevel($deterioration, $daysBack),
                'days_to_critical' => $this->calculateDaysToCritical($currentHealth, $deteriorationRate),
            ],
        ];
    }

    /**
     * ============================================
     * OVERALL TURBINE HEALTH
     * ============================================
     */
    public function calculateOverallHealth(int $turbineId, int $daysBack = 365): array
    {
        $components = [
            'main_bearing' => $this->calculateMainBearingHealth($turbineId, $daysBack),
            'gearbox' => $this->calculateGearboxHealth($turbineId, $daysBack),
            'generator' => $this->calculateGeneratorHealth($turbineId, $daysBack),
            'hydraulic_system' => $this->calculateHydraulicHealth($turbineId, $daysBack),
            'tower' => $this->calculateTowerHealth($turbineId, $daysBack),
        ];

        // Calculate weighted average (critical components weigh more)
        $weights = [
            'main_bearing' => 0.25,
            'gearbox' => 0.25,
            'generator' => 0.20,
            'hydraulic_system' => 0.20,
            'tower' => 0.10,
        ];

        $totalHealth = 0;
        $totalWeight = 0;

        foreach ($components as $name => $data) {
            if ($data['health_score'] !== null) {
                $totalHealth += $data['health_score'] * $weights[$name];
                $totalWeight += $weights[$name];
            }
        }

        $overallHealth = $totalWeight > 0 ? $totalHealth / $totalWeight : 0;

        return [
            'overall_health_score' => round($overallHealth, 1),
            'status' => $this->getHealthStatus($overallHealth),
            'critical_components' => $this->getCriticalComponents($components),
        ];
    }

    // ============================================
    // PENALTY CALCULATION METHODS
    // ============================================

    private function calculateVibrationPenalty(float $vibration, float $criticalThreshold, float $maxPenalty): float
    {
        // Convert from mm/s to m/s if needed (your data is in mms)
        $vibrationMS = $vibration / 1000;

        $penalty = ($vibrationMS / $criticalThreshold) * $maxPenalty;
        return min($maxPenalty, max(0, $penalty));
    }

    private function calculateTemperaturePenalty(float $temp, float $normalTemp, float $maxDeviation, float $maxPenalty): float
    {
        $deviation = max(0, $temp - $normalTemp);
        $penalty = ($deviation / $maxDeviation) * $maxPenalty;
        return min($maxPenalty, max(0, $penalty));
    }

    private function calculateOilPressurePenalty(float $pressure, float $normalPressure, float $maxPenalty): float
    {
        $deviation = max(0, $normalPressure - $pressure);
        $penalty = ($deviation / $normalPressure) * $maxPenalty;
        return min($maxPenalty, max(0, $penalty));
    }

    private function calculateHydraulicPressurePenalty(float $pressure, float $normalPressure, float $maxPenalty): float
    {
        $deviation = max(0, $normalPressure - $pressure);
        $penalty = ($deviation / $normalPressure) * $maxPenalty;
        return min($maxPenalty, max(0, $penalty));
    }

    private function calculateBladeImbalancePenalty(int $turbineId): float
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration) return 0;

        $blade1 = $vibration->blade1_vibration_mms / 1000;
        $blade2 = $vibration->blade2_vibration_mms / 1000;
        $blade3 = $vibration->blade3_vibration_mms / 1000;

        // Calculate standard deviation
        $mean = ($blade1 + $blade2 + $blade3) / 3;
        $variance = (pow($blade1 - $mean, 2) + pow($blade2 - $mean, 2) + pow($blade3 - $mean, 2)) / 3;
        $stdDev = sqrt($variance);

        // Penalty based on imbalance
        return min(10, $stdDev * 5);
    }

    /**
     * Calculate alarm penalty based on historical alarms
     */
    private function calculateAlarmPenalty(int $turbineId, string $component, int $daysBack): float
    {
        $alarms = Alarm::where('turbine_id', $turbineId)
            ->where('component', 'LIKE', "%{$component}%")
            ->where('detected_at', '>=', Carbon::now()->subDays($daysBack))
            ->get();

        $penalty = 0;

        foreach ($alarms as $alarm) {
            $daysAgo = Carbon::parse($alarm->detected_at)->diffInDays(now());

            // Severity weight
            $severityWeight = match($alarm->severity) {
                'critical' => 15,
                'high' => 10,
                'medium' => 5,
                'low' => 2,
                default => 1,
            };

            // Recency weight (decay over time)
            $recencyWeight = match(true) {
                $daysAgo <= 30 => 1.0,
                $daysAgo <= 90 => 0.7,
                $daysAgo <= 180 => 0.4,
                default => 0.2,
            };

            // Unresolved alarms count double
            $resolutionWeight = $alarm->status === 'resolved' ? 1.0 : 2.0;

            $penalty += $severityWeight * $recencyWeight * $resolutionWeight;
        }

        return min(30, $penalty); // Cap at 30 points
    }

    // ============================================
    // DATA RETRIEVAL METHODS
    // ============================================

    private function getCurrentMainBearingData(int $turbineId): ?array
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        $temperature = TemperatureReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration || !$temperature) return null;

        return [
            'vibration_rms' => $vibration->main_bearing_vibration_rms_mms,
            'temperature' => $temperature->main_bearing_temp_c,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getHistoricalMainBearingData(int $turbineId, int $daysBack): ?array
    {
        $targetDate = Carbon::now()->subDays($daysBack);

        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        $temperature = TemperatureReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration || !$temperature) return null;

        return [
            'vibration_rms' => $vibration->main_bearing_vibration_rms_mms,
            'temperature' => $temperature->main_bearing_temp_c,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getCurrentGearboxData(int $turbineId): ?array
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        $temperature = TemperatureReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        $hydraulic = HydraulicReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration || !$temperature || !$hydraulic) return null;

        return [
            'vibration_axial' => $vibration->gearbox_vibration_axial_mms,
            'vibration_radial' => $vibration->gearbox_vibration_radial_mms,
            'bearing_temp' => $temperature->gearbox_bearing_temp_c,
            'oil_temp' => $temperature->gearbox_oil_temp_c,
            'oil_pressure' => $hydraulic->gearbox_oil_pressure_bar,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getHistoricalGearboxData(int $turbineId, int $daysBack): ?array
    {
        $targetDate = Carbon::now()->subDays($daysBack);

        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        $temperature = TemperatureReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        $hydraulic = HydraulicReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration || !$temperature || !$hydraulic) return null;

        return [
            'vibration_axial' => $vibration->gearbox_vibration_axial_mms,
            'vibration_radial' => $vibration->gearbox_vibration_radial_mms,
            'bearing_temp' => $temperature->gearbox_bearing_temp_c,
            'oil_temp' => $temperature->gearbox_oil_temp_c,
            'oil_pressure' => $hydraulic->gearbox_oil_pressure_bar,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getCurrentGeneratorData(int $turbineId): ?array
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        $temperature = TemperatureReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration || !$temperature) return null;

        return [
            'vibration_de' => $vibration->generator_vibration_de_mms,
            'vibration_nde' => $vibration->generator_vibration_nde_mms,
            'bearing1_temp' => $temperature->generator_bearing1_temp_c,
            'bearing2_temp' => $temperature->generator_bearing2_temp_c,
            'stator_temp' => $temperature->generator_stator_temp_c,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getHistoricalGeneratorData(int $turbineId, int $daysBack): ?array
    {
        $targetDate = Carbon::now()->subDays($daysBack);

        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        $temperature = TemperatureReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration || !$temperature) return null;

        return [
            'vibration_de' => $vibration->generator_vibration_de_mms,
            'vibration_nde' => $vibration->generator_vibration_nde_mms,
            'bearing1_temp' => $temperature->generator_bearing1_temp_c,
            'bearing2_temp' => $temperature->generator_bearing2_temp_c,
            'stator_temp' => $temperature->generator_stator_temp_c,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getCurrentBladeData(int $turbineId, int $bladeNumber): ?array
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration) return null;

        $bladeField = "blade{$bladeNumber}_vibration_mms";

        return [
            'vibration' => $vibration->$bladeField,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getHistoricalBladeData(int $turbineId, int $bladeNumber, int $daysBack): ?array
    {
        $targetDate = Carbon::now()->subDays($daysBack);

        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration) return null;

        $bladeField = "blade{$bladeNumber}_vibration_mms";

        return [
            'vibration' => $vibration->$bladeField,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getCurrentHydraulicData(int $turbineId): ?array
    {
        $hydraulic = HydraulicReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$hydraulic) return null;

        return [
            'pressure' => $hydraulic->hydraulic_pressure_bar,
            'timestamp' => $hydraulic->reading_timestamp,
        ];
    }

    private function getHistoricalHydraulicData(int $turbineId, int $daysBack): ?array
    {
        $targetDate = Carbon::now()->subDays($daysBack);

        $hydraulic = HydraulicReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        if (!$hydraulic) return null;

        return [
            'pressure' => $hydraulic->hydraulic_pressure_bar,
            'timestamp' => $hydraulic->reading_timestamp,
        ];
    }

    private function getCurrentTowerData(int $turbineId): ?array
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration) return null;

        return [
            'vibration_fa' => $vibration->tower_vibration_fa_mms,
            'vibration_ss' => $vibration->tower_vibration_ss_mms,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    private function getHistoricalTowerData(int $turbineId, int $daysBack): ?array
    {
        $targetDate = Carbon::now()->subDays($daysBack);

        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '<=', $targetDate)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration) return null;

        return [
            'vibration_fa' => $vibration->tower_vibration_fa_mms,
            'vibration_ss' => $vibration->tower_vibration_ss_mms,
            'timestamp' => $vibration->reading_timestamp,
        ];
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    private function getHealthStatus(float $score): string
    {
        return match(true) {
            $score >= 80 => 'EXCELLENT',
            $score >= 70 => 'GOOD',
            $score >= 50 => 'FAIR',
            $score >= 30 => 'POOR',
            default => 'CRITICAL',
        };
    }

    private function getDeteriorationLevel(?float $deterioration, int $daysBack): ?string
    {
        if ($deterioration === null) return null;

        // Normalize to annual rate
        $annualDeterioration = ($deterioration / $daysBack) * 365;

        return match(true) {
            $annualDeterioration <= 10 => 'STABLE',
            $annualDeterioration <= 25 => 'SLOW_DECLINE',
            $annualDeterioration <= 40 => 'MODERATE_DECLINE',
            $annualDeterioration <= 60 => 'RAPID_DECLINE',
            default => 'CRITICAL_DECLINE',
        };
    }

    private function calculateDaysToCritical(float $currentHealth, ?float $deteriorationRate): ?int
    {
        if ($deteriorationRate === null || $deteriorationRate <= 0) return null;

        $criticalThreshold = 30;
        $pointsUntilCritical = $currentHealth - $criticalThreshold;

        if ($pointsUntilCritical <= 0) return 0;

        return (int) ceil($pointsUntilCritical / $deteriorationRate);
    }

    private function getCriticalComponents(array $components): array
    {
        $critical = [];

        foreach ($components as $name => $data) {
            if ($data['health_score'] < 50) {
                $critical[] = [
                    'component' => $name,
                    'health_score' => $data['health_score'],
                    'status' => $data['status'],
                ];
            }
        }

        return $critical;
    }

    private function createEmptyHealthData(string $component): array
    {
        return [
            'component' => $component,
            'health_score' => null,
            'status' => 'NO_DATA',
            'current_data' => null,
            'penalties' => null,
            'trend_analysis' => null,
        ];
    }
}
