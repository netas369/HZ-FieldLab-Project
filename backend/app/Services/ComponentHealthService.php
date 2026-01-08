<?php

namespace App\Services;

use App\Models\Alarm;
use App\Models\VibrationReading;
use App\Models\TemperatureReading;
use App\Models\HydraulicReading;
use App\Models\Threshold;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ComponentHealthService
{
    // Cached thresholds
    protected array $thresholds = [];

    // Minimum data points required for reliable trend analysis
    const MIN_DATA_POINTS_FOR_TREND = 10;
    const IDEAL_DATA_POINTS = 30;

    // Sampling strategies
    const SAMPLE_DAILY = 'daily';
    const SAMPLE_WEEKLY = 'weekly';
    const SAMPLE_MONTHLY = 'monthly';

    public function __construct()
    {
        $this->loadThresholds();
    }

    /**
     * Load all thresholds from database (cached for 1 hour)
     */
    protected function loadThresholds(): void
    {
        $this->thresholds = Cache::remember('component_thresholds', 3600, function () {
            return Threshold::all()->keyBy('component_name')->toArray();
        });
    }

    /**
     * Get threshold for a component
     */
    protected function getThreshold(string $componentName): ?array
    {
        return $this->thresholds[$componentName] ?? null;
    }

    /**
     * ============================================
     * SMART DATA AVAILABILITY ANALYSIS
     * ============================================
     */
    public function analyzeDataAvailability(int $turbineId): array
    {
        // Check each sensor table
        $vibrationData = $this->getDataRange(VibrationReading::class, $turbineId);
        $temperatureData = $this->getDataRange(TemperatureReading::class, $turbineId);
        $hydraulicData = $this->getDataRange(HydraulicReading::class, $turbineId);

        $oldestDate = min(
            $vibrationData['oldest'] ?? now(),
            $temperatureData['oldest'] ?? now(),
            $hydraulicData['oldest'] ?? now()
        );

        $newestDate = max(
            $vibrationData['newest'] ?? now(),
            $temperatureData['newest'] ?? now(),
            $hydraulicData['newest'] ?? now()
        );

        $totalDays = Carbon::parse($oldestDate)->diffInDays(Carbon::parse($newestDate));

        // Determine optimal analysis period and sampling strategy
        $analysisConfig = $this->determineAnalysisConfig($totalDays, min(
            $vibrationData['count'] ?? 0,
            $temperatureData['count'] ?? 0,
            $hydraulicData['count'] ?? 0
        ));

        return [
            'turbine_id' => $turbineId,
            'data_range' => [
                'oldest_reading' => $oldestDate,
                'newest_reading' => $newestDate,
                'total_days' => $totalDays,
            ],
            'sensor_counts' => [
                'vibration' => $vibrationData['count'] ?? 0,
                'temperature' => $temperatureData['count'] ?? 0,
                'hydraulic' => $hydraulicData['count'] ?? 0,
            ],
            'analysis_config' => $analysisConfig,
        ];
    }

    protected function getDataRange(string $modelClass, int $turbineId): array
    {
        $oldest = $modelClass::where('turbine_id', $turbineId)
            ->orderBy('reading_timestamp', 'asc')
            ->value('reading_timestamp');

        $newest = $modelClass::where('turbine_id', $turbineId)
            ->orderBy('reading_timestamp', 'desc')
            ->value('reading_timestamp');

        $count = $modelClass::where('turbine_id', $turbineId)->count();

        return [
            'oldest' => $oldest,
            'newest' => $newest,
            'count' => $count,
        ];
    }

    /**
     * Determine optimal analysis configuration based on available data
     */
    protected function determineAnalysisConfig(int $totalDays, int $dataPoints): array
    {
        // Determine recommended analysis period
        if ($totalDays >= 365 && $dataPoints >= 100) {
            $recommendedPeriod = 365;
            $samplingStrategy = self::SAMPLE_WEEKLY;
            $confidenceLevel = 'HIGH';
        } elseif ($totalDays >= 180 && $dataPoints >= 50) {
            $recommendedPeriod = 180;
            $samplingStrategy = self::SAMPLE_WEEKLY;
            $confidenceLevel = 'MEDIUM';
        } elseif ($totalDays >= 90 && $dataPoints >= 30) {
            $recommendedPeriod = 90;
            $samplingStrategy = self::SAMPLE_DAILY;
            $confidenceLevel = 'MEDIUM';
        } elseif ($totalDays >= 30 && $dataPoints >= 15) {
            $recommendedPeriod = 30;
            $samplingStrategy = self::SAMPLE_DAILY;
            $confidenceLevel = 'LOW';
        } else {
            $recommendedPeriod = $totalDays;
            $samplingStrategy = self::SAMPLE_DAILY;
            $confidenceLevel = 'INSUFFICIENT';
        }

        return [
            'recommended_period_days' => $recommendedPeriod,
            'sampling_strategy' => $samplingStrategy,
            'confidence_level' => $confidenceLevel,
            'min_data_points_required' => self::MIN_DATA_POINTS_FOR_TREND,
            'actual_data_points' => $dataPoints,
            'can_calculate_trend' => $dataPoints >= self::MIN_DATA_POINTS_FOR_TREND,
        ];
    }

    /**
     * ============================================
     * MAIN HEALTH CALCULATION (SMART VERSION)
     * ============================================
     */
    public function calculateAllComponentsHealth(int $turbineId, ?int $daysBack = null): array
    {
        // Auto-detect optimal analysis period if not specified
        $dataAvailability = $this->analyzeDataAvailability($turbineId);

        if ($daysBack === null) {
            $daysBack = $dataAvailability['analysis_config']['recommended_period_days'];
        }

        // Cap at available data
        $daysBack = min($daysBack, $dataAvailability['data_range']['total_days']);
        $daysBack = max(7, $daysBack); // Minimum 7 days

        return [
            'turbine_id' => $turbineId,
            'calculation_timestamp' => now()->toDateTimeString(),
            'analysis_period' => [
                'days_analyzed' => $daysBack,
                'confidence_level' => $dataAvailability['analysis_config']['confidence_level'],
                'data_availability' => $dataAvailability['data_range'],
            ],
            'components' => [
                'main_bearing' => $this->calculateMainBearingHealthSmart($turbineId, $daysBack),
                'gearbox' => $this->calculateGearboxHealthSmart($turbineId, $daysBack),
                'generator' => $this->calculateGeneratorHealthSmart($turbineId, $daysBack),
                'blade_1' => $this->calculateBladeHealthSmart($turbineId, 1, $daysBack),
                'blade_2' => $this->calculateBladeHealthSmart($turbineId, 2, $daysBack),
                'blade_3' => $this->calculateBladeHealthSmart($turbineId, 3, $daysBack),
                'hydraulic_system' => $this->calculateHydraulicHealthSmart($turbineId, $daysBack),
                'tower' => $this->calculateTowerHealthSmart($turbineId, $daysBack),
            ],
            'overall_health' => $this->calculateOverallHealth($turbineId, $daysBack),
        ];
    }

    /**
     * ============================================
     * MAIN BEARING HEALTH (SMART)
     * ============================================
     */
    public function calculateMainBearingHealthSmart(int $turbineId, int $daysBack): array
    {
        $component = 'main_bearing';

        // Get thresholds from database
        $vibThreshold = $this->getThreshold('main_bearing_vibration_rms');
        $tempThreshold = $this->getThreshold('main_bearing_temp');

        // Get time-series data for trend analysis
        $vibrationSeries = $this->getTimeSeries(
            VibrationReading::class,
            $turbineId,
            'main_bearing_vibration_rms_mms',
            $daysBack
        );

        $temperatureSeries = $this->getTimeSeries(
            TemperatureReading::class,
            $turbineId,
            'main_bearing_temp_c',
            $daysBack
        );

        if (empty($vibrationSeries) || empty($temperatureSeries)) {
            return $this->createEmptyHealthData($component);
        }

        // Current values (latest readings)
        $currentVibration = end($vibrationSeries)['value'];
        $currentTemp = end($temperatureSeries)['value'];

        // Calculate penalties using database thresholds
        $vibrationPenalty = $this->calculatePenaltyFromThreshold(
            $currentVibration,
            $vibThreshold,
            40, // max penalty
            'high_is_bad'
        );

        $tempPenalty = $this->calculatePenaltyFromThreshold(
            $currentTemp,
            $tempThreshold,
            30,
            'high_is_bad'
        );

        // Alarm penalty
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, $component, $daysBack);

        // Current health score
        $currentHealth = max(0, 100 - min(100, $vibrationPenalty + $tempPenalty + $alarmPenalty));

        // SMART TREND ANALYSIS
        $vibrationTrend = $this->analyzeTimeSeries($vibrationSeries, $vibThreshold);
        $temperatureTrend = $this->analyzeTimeSeries($temperatureSeries, $tempThreshold);

        // Combined deterioration analysis
        $deteriorationAnalysis = $this->combineDeterioration(
            [$vibrationTrend, $temperatureTrend],
            $currentHealth,
            $daysBack
        );

        return [
            'component' => $component,
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration_rms' => round($currentVibration, 3),
                'temperature' => round($currentTemp, 1),
                'timestamp' => end($vibrationSeries)['timestamp'],
            ],
            'thresholds_used' => [
                'vibration' => [
                    'normal_max' => $vibThreshold['normal_max'] ?? null,
                    'warning_max' => $vibThreshold['warning_max'] ?? null,
                    'critical_max' => $vibThreshold['critical_max'] ?? null,
                ],
                'temperature' => [
                    'normal_max' => $tempThreshold['normal_max'] ?? null,
                    'warning_max' => $tempThreshold['warning_max'] ?? null,
                    'critical_max' => $tempThreshold['critical_max'] ?? null,
                ],
            ],
            'penalties' => [
                'vibration' => round($vibrationPenalty, 1),
                'temperature' => round($tempPenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($vibrationPenalty + $tempPenalty + $alarmPenalty, 1),
            ],
            'sensor_trends' => [
                'vibration' => $vibrationTrend,
                'temperature' => $temperatureTrend,
            ],
            'deterioration_analysis' => $deteriorationAnalysis,
        ];
    }

    /**
     * ============================================
     * GEARBOX HEALTH (SMART)
     * ============================================
     */
    public function calculateGearboxHealthSmart(int $turbineId, int $daysBack): array
    {
        $component = 'gearbox';

        // Get thresholds
        $vibAxialThreshold = $this->getThreshold('gearbox_vibration_axial');
        $vibRadialThreshold = $this->getThreshold('gearbox_vibration_radial');
        $bearingTempThreshold = $this->getThreshold('gearbox_bearing_temp');
        $oilTempThreshold = $this->getThreshold('gearbox_oil_temp');
        $oilPressureThreshold = $this->getThreshold('gearbox_oil_pressure');

        // Get time-series data
        $vibAxialSeries = $this->getTimeSeries(VibrationReading::class, $turbineId, 'gearbox_vibration_axial_mms', $daysBack);
        $vibRadialSeries = $this->getTimeSeries(VibrationReading::class, $turbineId, 'gearbox_vibration_radial_mms', $daysBack);
        $bearingTempSeries = $this->getTimeSeries(TemperatureReading::class, $turbineId, 'gearbox_bearing_temp_c', $daysBack);
        $oilTempSeries = $this->getTimeSeries(TemperatureReading::class, $turbineId, 'gearbox_oil_temp_c', $daysBack);
        $oilPressureSeries = $this->getTimeSeries(HydraulicReading::class, $turbineId, 'gearbox_oil_pressure_bar', $daysBack);

        if (empty($vibAxialSeries) || empty($oilPressureSeries)) {
            return $this->createEmptyHealthData($component);
        }

        // Current values
        $currentVibAxial = end($vibAxialSeries)['value'];
        $currentVibRadial = end($vibRadialSeries)['value'];
        $currentBearingTemp = end($bearingTempSeries)['value'];
        $currentOilTemp = end($oilTempSeries)['value'];
        $currentOilPressure = end($oilPressureSeries)['value'];

        // Calculate penalties
        $vibAxialPenalty = $this->calculatePenaltyFromThreshold($currentVibAxial, $vibAxialThreshold, 20, 'high_is_bad');
        $vibRadialPenalty = $this->calculatePenaltyFromThreshold($currentVibRadial, $vibRadialThreshold, 20, 'high_is_bad');
        $bearingTempPenalty = $this->calculatePenaltyFromThreshold($currentBearingTemp, $bearingTempThreshold, 15, 'high_is_bad');
        $oilTempPenalty = $this->calculatePenaltyFromThreshold($currentOilTemp, $oilTempThreshold, 15, 'high_is_bad');
        // Oil pressure: LOW is bad (inverse logic)
        $oilPressurePenalty = $this->calculatePenaltyFromThreshold($currentOilPressure, $oilPressureThreshold, 20, 'low_is_bad');

        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, $component, $daysBack);

        $totalPenalty = $vibAxialPenalty + $vibRadialPenalty + $bearingTempPenalty + $oilTempPenalty + $oilPressurePenalty + $alarmPenalty;
        $currentHealth = max(0, 100 - min(100, $totalPenalty));

        // Trend analysis
        $trends = [
            'vibration_axial' => $this->analyzeTimeSeries($vibAxialSeries, $vibAxialThreshold),
            'vibration_radial' => $this->analyzeTimeSeries($vibRadialSeries, $vibRadialThreshold),
            'bearing_temp' => $this->analyzeTimeSeries($bearingTempSeries, $bearingTempThreshold),
            'oil_temp' => $this->analyzeTimeSeries($oilTempSeries, $oilTempThreshold),
            'oil_pressure' => $this->analyzeTimeSeries($oilPressureSeries, $oilPressureThreshold, 'low_is_bad'),
        ];

        $deteriorationAnalysis = $this->combineDeterioration(
            array_values($trends),
            $currentHealth,
            $daysBack
        );

        return [
            'component' => $component,
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration_axial' => round($currentVibAxial, 3),
                'vibration_radial' => round($currentVibRadial, 3),
                'bearing_temp' => round($currentBearingTemp, 1),
                'oil_temp' => round($currentOilTemp, 1),
                'oil_pressure' => round($currentOilPressure, 2),
                'timestamp' => end($vibAxialSeries)['timestamp'],
            ],
            'penalties' => [
                'vibration_axial' => round($vibAxialPenalty, 1),
                'vibration_radial' => round($vibRadialPenalty, 1),
                'bearing_temp' => round($bearingTempPenalty, 1),
                'oil_temp' => round($oilTempPenalty, 1),
                'oil_pressure' => round($oilPressurePenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($totalPenalty, 1),
            ],
            'sensor_trends' => $trends,
            'deterioration_analysis' => $deteriorationAnalysis,
        ];
    }

    /**
     * ============================================
     * GENERATOR HEALTH (SMART)
     * ============================================
     */
    public function calculateGeneratorHealthSmart(int $turbineId, int $daysBack): array
    {
        $component = 'generator';

        $vibDEThreshold = $this->getThreshold('generator_vibration_de');
        $vibNDEThreshold = $this->getThreshold('generator_vibration_nde');
        $bearing1TempThreshold = $this->getThreshold('generator_bearing1_temp');
        $bearing2TempThreshold = $this->getThreshold('generator_bearing2_temp');
        $statorTempThreshold = $this->getThreshold('generator_stator_temp');

        $vibDESeries = $this->getTimeSeries(VibrationReading::class, $turbineId, 'generator_vibration_de_mms', $daysBack);
        $vibNDESeries = $this->getTimeSeries(VibrationReading::class, $turbineId, 'generator_vibration_nde_mms', $daysBack);
        $bearing1TempSeries = $this->getTimeSeries(TemperatureReading::class, $turbineId, 'generator_bearing1_temp_c', $daysBack);
        $bearing2TempSeries = $this->getTimeSeries(TemperatureReading::class, $turbineId, 'generator_bearing2_temp_c', $daysBack);
        $statorTempSeries = $this->getTimeSeries(TemperatureReading::class, $turbineId, 'generator_stator_temp_c', $daysBack);

        if (empty($vibDESeries)) {
            return $this->createEmptyHealthData($component);
        }

        $currentVibDE = end($vibDESeries)['value'];
        $currentVibNDE = end($vibNDESeries)['value'];
        $currentBearing1Temp = end($bearing1TempSeries)['value'];
        $currentBearing2Temp = end($bearing2TempSeries)['value'];
        $currentStatorTemp = end($statorTempSeries)['value'];

        $vibDEPenalty = $this->calculatePenaltyFromThreshold($currentVibDE, $vibDEThreshold, 15, 'high_is_bad');
        $vibNDEPenalty = $this->calculatePenaltyFromThreshold($currentVibNDE, $vibNDEThreshold, 15, 'high_is_bad');
        $bearing1TempPenalty = $this->calculatePenaltyFromThreshold($currentBearing1Temp, $bearing1TempThreshold, 10, 'high_is_bad');
        $bearing2TempPenalty = $this->calculatePenaltyFromThreshold($currentBearing2Temp, $bearing2TempThreshold, 10, 'high_is_bad');
        $statorTempPenalty = $this->calculatePenaltyFromThreshold($currentStatorTemp, $statorTempThreshold, 10, 'high_is_bad');
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, $component, $daysBack);

        $totalPenalty = $vibDEPenalty + $vibNDEPenalty + $bearing1TempPenalty + $bearing2TempPenalty + $statorTempPenalty + $alarmPenalty;
        $currentHealth = max(0, 100 - min(100, $totalPenalty));

        $trends = [
            'vibration_de' => $this->analyzeTimeSeries($vibDESeries, $vibDEThreshold),
            'vibration_nde' => $this->analyzeTimeSeries($vibNDESeries, $vibNDEThreshold),
            'bearing1_temp' => $this->analyzeTimeSeries($bearing1TempSeries, $bearing1TempThreshold),
            'bearing2_temp' => $this->analyzeTimeSeries($bearing2TempSeries, $bearing2TempThreshold),
            'stator_temp' => $this->analyzeTimeSeries($statorTempSeries, $statorTempThreshold),
        ];

        $deteriorationAnalysis = $this->combineDeterioration(array_values($trends), $currentHealth, $daysBack);

        return [
            'component' => $component,
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration_de' => round($currentVibDE, 3),
                'vibration_nde' => round($currentVibNDE, 3),
                'bearing1_temp' => round($currentBearing1Temp, 1),
                'bearing2_temp' => round($currentBearing2Temp, 1),
                'stator_temp' => round($currentStatorTemp, 1),
                'timestamp' => end($vibDESeries)['timestamp'],
            ],
            'penalties' => [
                'vibration_de' => round($vibDEPenalty, 1),
                'vibration_nde' => round($vibNDEPenalty, 1),
                'bearing1_temp' => round($bearing1TempPenalty, 1),
                'bearing2_temp' => round($bearing2TempPenalty, 1),
                'stator_temp' => round($statorTempPenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($totalPenalty, 1),
            ],
            'sensor_trends' => $trends,
            'deterioration_analysis' => $deteriorationAnalysis,
        ];
    }

    /**
     * ============================================
     * BLADE HEALTH (SMART)
     * ============================================
     */
    public function calculateBladeHealthSmart(int $turbineId, int $bladeNumber, int $daysBack): array
    {
        $component = "blade_{$bladeNumber}";
        $fieldName = "blade{$bladeNumber}_vibration_mms";

        $vibThreshold = $this->getThreshold("blade{$bladeNumber}_vibration");

        $vibrationSeries = $this->getTimeSeries(VibrationReading::class, $turbineId, $fieldName, $daysBack);

        if (empty($vibrationSeries)) {
            return $this->createEmptyHealthData($component);
        }

        $currentVibration = end($vibrationSeries)['value'];

        $vibrationPenalty = $this->calculatePenaltyFromThreshold($currentVibration, $vibThreshold, 50, 'high_is_bad');
        $imbalancePenalty = $this->calculateBladeImbalancePenalty($turbineId, $bladeNumber);
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, "blade{$bladeNumber}", $daysBack);

        $totalPenalty = $vibrationPenalty + $imbalancePenalty + $alarmPenalty;
        $currentHealth = max(0, 100 - min(100, $totalPenalty));

        $vibrationTrend = $this->analyzeTimeSeries($vibrationSeries, $vibThreshold);
        $deteriorationAnalysis = $this->combineDeterioration([$vibrationTrend], $currentHealth, $daysBack);

        return [
            'component' => $component,
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration' => round($currentVibration, 3),
                'timestamp' => end($vibrationSeries)['timestamp'],
            ],
            'penalties' => [
                'vibration' => round($vibrationPenalty, 1),
                'imbalance' => round($imbalancePenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($totalPenalty, 1),
            ],
            'sensor_trends' => [
                'vibration' => $vibrationTrend,
            ],
            'deterioration_analysis' => $deteriorationAnalysis,
        ];
    }

    /**
     * ============================================
     * HYDRAULIC SYSTEM HEALTH (SMART)
     * ============================================
     */
    public function calculateHydraulicHealthSmart(int $turbineId, int $daysBack): array
    {
        $component = 'hydraulic_system';

        $pressureThreshold = $this->getThreshold('hydraulic_pressure');

        $pressureSeries = $this->getTimeSeries(HydraulicReading::class, $turbineId, 'hydraulic_pressure_bar', $daysBack);

        if (empty($pressureSeries)) {
            return $this->createEmptyHealthData($component);
        }

        $currentPressure = end($pressureSeries)['value'];

        // Hydraulic pressure: both too LOW and too HIGH are problems
        $pressurePenalty = $this->calculatePenaltyFromThreshold($currentPressure, $pressureThreshold, 50, 'both_bad');
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, 'hydraulic', $daysBack);

        $totalPenalty = $pressurePenalty + $alarmPenalty;
        $currentHealth = max(0, 100 - min(100, $totalPenalty));

        $pressureTrend = $this->analyzeTimeSeries($pressureSeries, $pressureThreshold, 'both_bad');
        $deteriorationAnalysis = $this->combineDeterioration([$pressureTrend], $currentHealth, $daysBack);

        return [
            'component' => $component,
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'pressure' => round($currentPressure, 2),
                'timestamp' => end($pressureSeries)['timestamp'],
            ],
            'thresholds_used' => [
                'pressure' => [
                    'normal_min' => $pressureThreshold['normal_min'] ?? null,
                    'normal_max' => $pressureThreshold['normal_max'] ?? null,
                    'warning_min' => $pressureThreshold['warning_min'] ?? null,
                    'warning_max' => $pressureThreshold['warning_max'] ?? null,
                    'critical_min' => $pressureThreshold['critical_min'] ?? null,
                    'critical_max' => $pressureThreshold['critical_max'] ?? null,
                ],
            ],
            'penalties' => [
                'pressure' => round($pressurePenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($totalPenalty, 1),
            ],
            'sensor_trends' => [
                'pressure' => $pressureTrend,
            ],
            'deterioration_analysis' => $deteriorationAnalysis,
        ];
    }

    /**
     * ============================================
     * TOWER HEALTH (SMART)
     * ============================================
     */
    public function calculateTowerHealthSmart(int $turbineId, int $daysBack): array
    {
        $component = 'tower';

        $vibFAThreshold = $this->getThreshold('tower_vibration_fa');
        $vibSSThreshold = $this->getThreshold('tower_vibration_ss');

        $vibFASeries = $this->getTimeSeries(VibrationReading::class, $turbineId, 'tower_vibration_fa_mms', $daysBack);
        $vibSSSeries = $this->getTimeSeries(VibrationReading::class, $turbineId, 'tower_vibration_ss_mms', $daysBack);

        if (empty($vibFASeries)) {
            return $this->createEmptyHealthData($component);
        }

        $currentVibFA = end($vibFASeries)['value'];
        $currentVibSS = end($vibSSSeries)['value'];

        $vibFAPenalty = $this->calculatePenaltyFromThreshold($currentVibFA, $vibFAThreshold, 25, 'high_is_bad');
        $vibSSPenalty = $this->calculatePenaltyFromThreshold($currentVibSS, $vibSSThreshold, 25, 'high_is_bad');
        $alarmPenalty = $this->calculateAlarmPenalty($turbineId, $component, $daysBack);

        $totalPenalty = $vibFAPenalty + $vibSSPenalty + $alarmPenalty;
        $currentHealth = max(0, 100 - min(100, $totalPenalty));

        $trends = [
            'vibration_fa' => $this->analyzeTimeSeries($vibFASeries, $vibFAThreshold),
            'vibration_ss' => $this->analyzeTimeSeries($vibSSSeries, $vibSSThreshold),
        ];

        $deteriorationAnalysis = $this->combineDeterioration(array_values($trends), $currentHealth, $daysBack);

        return [
            'component' => $component,
            'health_score' => round($currentHealth, 1),
            'status' => $this->getHealthStatus($currentHealth),
            'current_data' => [
                'vibration_fa' => round($currentVibFA, 3),
                'vibration_ss' => round($currentVibSS, 3),
                'timestamp' => end($vibFASeries)['timestamp'],
            ],
            'penalties' => [
                'vibration_fa' => round($vibFAPenalty, 1),
                'vibration_ss' => round($vibSSPenalty, 1),
                'alarms' => round($alarmPenalty, 1),
                'total' => round($totalPenalty, 1),
            ],
            'sensor_trends' => $trends,
            'deterioration_analysis' => $deteriorationAnalysis,
        ];
    }

    /**
     * ============================================
     * TIME SERIES ANALYSIS (THE SMART PART)
     * ============================================
     */

    /**
     * Get sampled time series data
     */
    protected function getTimeSeries(string $modelClass, int $turbineId, string $field, int $daysBack): array
    {
        $startDate = Carbon::now()->subDays($daysBack);

        // Get all readings in the period
        $readings = $modelClass::where('turbine_id', $turbineId)
            ->where('reading_timestamp', '>=', $startDate)
            ->orderBy('reading_timestamp', 'asc')
            ->select(['reading_timestamp', $field])
            ->get();

        if ($readings->isEmpty()) {
            return [];
        }

        // Sample appropriately based on data density
        $totalReadings = $readings->count();

        if ($totalReadings <= self::IDEAL_DATA_POINTS) {
            // Use all readings
            return $readings->map(fn($r) => [
                'timestamp' => $r->reading_timestamp,
                'value' => (float) $r->$field,
                'days_ago' => Carbon::parse($r->reading_timestamp)->diffInDays(now()),
            ])->toArray();
        }

        // Sample to ~30 data points for analysis
        $sampleInterval = max(1, (int) floor($totalReadings / self::IDEAL_DATA_POINTS));

        $sampled = [];
        foreach ($readings as $index => $reading) {
            if ($index % $sampleInterval === 0 || $index === $totalReadings - 1) {
                $sampled[] = [
                    'timestamp' => $reading->reading_timestamp,
                    'value' => (float) $reading->$field,
                    'days_ago' => Carbon::parse($reading->reading_timestamp)->diffInDays(now()),
                ];
            }
        }

        return $sampled;
    }

    /**
     * Analyze time series with linear regression and statistics
     */
    protected function analyzeTimeSeries(array $series, ?array $threshold, string $badDirection = 'high_is_bad'): array
    {
        if (count($series) < self::MIN_DATA_POINTS_FOR_TREND) {
            return [
                'has_sufficient_data' => false,
                'data_points' => count($series),
                'message' => 'Insufficient data points for trend analysis',
            ];
        }

        // Extract values and timestamps
        $values = array_column($series, 'value');
        $timestamps = array_map(fn($s) => strtotime($s['timestamp']), $series);

        // Normalize timestamps to days from start
        $startTime = min($timestamps);
        $days = array_map(fn($t) => ($t - $startTime) / 86400, $timestamps);

        // Linear regression: y = mx + b
        $regression = $this->linearRegression($days, $values);

        // Statistics
        $stats = $this->calculateStatistics($values);

        // Calculate current zone (where is the value relative to thresholds)
        $currentValue = end($values);
        $currentZone = $this->determineZone($currentValue, $threshold, $badDirection);

        // Predict when thresholds will be crossed
        $predictions = $this->predictThresholdCrossings($regression, $threshold, $currentValue, $badDirection);

        return [
            'has_sufficient_data' => true,
            'data_points' => count($series),
            'period_days' => max($days),
            'current_value' => round($currentValue, 3),
            'current_zone' => $currentZone,
            'statistics' => [
                'mean' => round($stats['mean'], 3),
                'std_dev' => round($stats['std_dev'], 3),
                'min' => round($stats['min'], 3),
                'max' => round($stats['max'], 3),
                'range' => round($stats['max'] - $stats['min'], 3),
            ],
            'trend' => [
                'slope_per_day' => round($regression['slope'], 6),
                'slope_per_year' => round($regression['slope'] * 365, 4),
                'direction' => $regression['slope'] > 0.0001 ? 'INCREASING' : ($regression['slope'] < -0.0001 ? 'DECREASING' : 'STABLE'),
                'r_squared' => round($regression['r_squared'], 3),
                'confidence' => $this->getTrendConfidence($regression['r_squared']),
                'intercept' => round($regression['intercept'], 3),
            ],
            'predictions' => $predictions,
        ];
    }

    /**
     * Linear regression calculation
     */
    protected function linearRegression(array $x, array $y): array
    {
        $n = count($x);

        if ($n === 0) {
            return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0];
        }

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);

        if ($denominator == 0) {
            return ['slope' => 0, 'intercept' => $sumY / $n, 'r_squared' => 0];
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        // Calculate R-squared
        $meanY = $sumY / $n;
        $ssTot = 0;
        $ssRes = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $ssTot += pow($y[$i] - $meanY, 2);
            $ssRes += pow($y[$i] - $predicted, 2);
        }

        $rSquared = $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => max(0, $rSquared),
        ];
    }

    /**
     * Calculate basic statistics
     */
    protected function calculateStatistics(array $values): array
    {
        $n = count($values);
        if ($n === 0) {
            return ['mean' => 0, 'std_dev' => 0, 'min' => 0, 'max' => 0];
        }

        $mean = array_sum($values) / $n;

        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= $n;

        return [
            'mean' => $mean,
            'std_dev' => sqrt($variance),
            'min' => min($values),
            'max' => max($values),
        ];
    }

    /**
     * Determine which zone the current value is in
     */
    protected function determineZone(float $value, ?array $threshold, string $badDirection): string
    {
        if (!$threshold) {
            return 'UNKNOWN';
        }

        // Check failed thresholds first
        if (isset($threshold['failed_max']) && $value >= $threshold['failed_max']) {
            return 'FAILED';
        }
        if (isset($threshold['failed_min']) && $threshold['failed_min'] > 0 && $value <= $threshold['failed_min']) {
            return 'FAILED';
        }

        // Check critical thresholds
        if (isset($threshold['critical_max']) && $value >= $threshold['critical_max']) {
            return 'CRITICAL';
        }
        if (isset($threshold['critical_min']) && $threshold['critical_min'] > 0 && $value <= $threshold['critical_min']) {
            return 'CRITICAL';
        }

        // Check warning thresholds
        if (isset($threshold['warning_max']) && $value >= $threshold['warning_max']) {
            return 'WARNING';
        }
        if (isset($threshold['warning_min']) && $threshold['warning_min'] > 0 && $value <= $threshold['warning_min']) {
            return 'WARNING';
        }

        // Check normal thresholds
        if (isset($threshold['normal_max']) && $value >= $threshold['normal_max']) {
            return 'ELEVATED';
        }
        if (isset($threshold['normal_min']) && $threshold['normal_min'] > 0 && $value <= $threshold['normal_min']) {
            return 'LOW';
        }

        return 'NORMAL';
    }

    /**
     * Predict when thresholds will be crossed based on trend
     */
    protected function predictThresholdCrossings(array $regression, ?array $threshold, float $currentValue, string $badDirection): array
    {
        if (!$threshold || abs($regression['slope']) < 0.0000001) {
            return [
                'can_predict' => false,
                'reason' => abs($regression['slope']) < 0.0000001 ? 'No significant trend' : 'No thresholds defined',
            ];
        }

        $predictions = ['can_predict' => true];
        $slope = $regression['slope'];

        // Only predict if trending in the "bad" direction
        $isTrendingBad = ($badDirection === 'high_is_bad' && $slope > 0) ||
            ($badDirection === 'low_is_bad' && $slope < 0);

        if (!$isTrendingBad && $badDirection !== 'both_bad') {
            $predictions['trend_direction'] = 'IMPROVING';
            $predictions['days_to_warning'] = null;
            $predictions['days_to_critical'] = null;
            $predictions['days_to_failed'] = null;
            return $predictions;
        }

        $predictions['trend_direction'] = 'DETERIORATING';

        // Calculate days to each threshold
        if ($badDirection === 'high_is_bad' || ($badDirection === 'both_bad' && $slope > 0)) {
            if (isset($threshold['warning_max']) && $currentValue < $threshold['warning_max']) {
                $predictions['days_to_warning'] = $this->calculateDaysToThreshold($currentValue, $threshold['warning_max'], $slope);
            }
            if (isset($threshold['critical_max']) && $currentValue < $threshold['critical_max']) {
                $predictions['days_to_critical'] = $this->calculateDaysToThreshold($currentValue, $threshold['critical_max'], $slope);
            }
            if (isset($threshold['failed_max']) && $currentValue < $threshold['failed_max']) {
                $predictions['days_to_failed'] = $this->calculateDaysToThreshold($currentValue, $threshold['failed_max'], $slope);
            }
        }

        if ($badDirection === 'low_is_bad' || ($badDirection === 'both_bad' && $slope < 0)) {
            if (isset($threshold['warning_min']) && $threshold['warning_min'] > 0 && $currentValue > $threshold['warning_min']) {
                $predictions['days_to_warning'] = $this->calculateDaysToThreshold($currentValue, $threshold['warning_min'], $slope);
            }
            if (isset($threshold['critical_min']) && $threshold['critical_min'] > 0 && $currentValue > $threshold['critical_min']) {
                $predictions['days_to_critical'] = $this->calculateDaysToThreshold($currentValue, $threshold['critical_min'], $slope);
            }
            if (isset($threshold['failed_min']) && $threshold['failed_min'] > 0 && $currentValue > $threshold['failed_min']) {
                $predictions['days_to_failed'] = $this->calculateDaysToThreshold($currentValue, $threshold['failed_min'], $slope);
            }
        }

        return $predictions;
    }

    protected function calculateDaysToThreshold(float $currentValue, float $threshold, float $slope): ?int
    {
        if ($slope == 0) return null;

        $days = ($threshold - $currentValue) / $slope;

        if ($days < 0) return null; // Already past threshold
        if ($days > 3650) return null; // More than 10 years - not meaningful

        return (int) ceil($days);
    }

    /**
     * Combine deterioration analysis from multiple sensors
     */
    protected function combineDeterioration(array $sensorTrends, float $currentHealth, int $daysBack): array
    {
        $validTrends = array_filter($sensorTrends, fn($t) => $t['has_sufficient_data'] ?? false);

        if (empty($validTrends)) {
            return [
                'can_analyze' => false,
                'reason' => 'Insufficient data across sensors',
            ];
        }

        // Find the worst-case predictions
        $minDaysToWarning = null;
        $minDaysToCritical = null;
        $minDaysToFailed = null;
        $worstTrendingComponent = null;
        $worstTrendRate = 0;

        foreach ($validTrends as $trend) {
            $predictions = $trend['predictions'] ?? [];

            if (isset($predictions['days_to_warning']) && $predictions['days_to_warning'] !== null) {
                if ($minDaysToWarning === null || $predictions['days_to_warning'] < $minDaysToWarning) {
                    $minDaysToWarning = $predictions['days_to_warning'];
                }
            }
            if (isset($predictions['days_to_critical']) && $predictions['days_to_critical'] !== null) {
                if ($minDaysToCritical === null || $predictions['days_to_critical'] < $minDaysToCritical) {
                    $minDaysToCritical = $predictions['days_to_critical'];
                }
            }
            if (isset($predictions['days_to_failed']) && $predictions['days_to_failed'] !== null) {
                if ($minDaysToFailed === null || $predictions['days_to_failed'] < $minDaysToFailed) {
                    $minDaysToFailed = $predictions['days_to_failed'];
                }
            }

            // Track worst deterioration rate
            $slopePerYear = abs($trend['trend']['slope_per_year'] ?? 0);
            if ($slopePerYear > $worstTrendRate) {
                $worstTrendRate = $slopePerYear;
            }
        }

        // Calculate average R² for confidence
        $rSquaredValues = array_map(fn($t) => $t['trend']['r_squared'] ?? 0, $validTrends);
        $avgRSquared = array_sum($rSquaredValues) / count($rSquaredValues);

        // Determine overall deterioration level
        $deteriorationLevel = $this->determineDeteriorationLevel($minDaysToCritical, $currentHealth, $avgRSquared);

        return [
            'can_analyze' => true,
            'sensors_analyzed' => count($validTrends),
            'analysis_confidence' => $this->getTrendConfidence($avgRSquared),
            'avg_r_squared' => round($avgRSquared, 3),
            'current_health_score' => round($currentHealth, 1),
            'deterioration_level' => $deteriorationLevel,
            'predictions' => [
                'days_to_warning' => $minDaysToWarning,
                'days_to_critical' => $minDaysToCritical,
                'days_to_failed' => $minDaysToFailed,
            ],
            'recommendation' => $this->getRecommendation($deteriorationLevel, $minDaysToCritical, $currentHealth),
        ];
    }

    protected function determineDeteriorationLevel(?int $daysToCritical, float $currentHealth, float $rSquared): string
    {
        // If already critical
        if ($currentHealth < 30) {
            return 'CRITICAL_NOW';
        }

        // If no prediction possible or very far out
        if ($daysToCritical === null || $daysToCritical > 730) {
            if ($currentHealth >= 80) return 'STABLE';
            if ($currentHealth >= 60) return 'SLOW_DECLINE';
            return 'MODERATE_DECLINE';
        }

        // Based on days to critical
        if ($daysToCritical <= 30) return 'CRITICAL_IMMINENT';
        if ($daysToCritical <= 90) return 'RAPID_DECLINE';
        if ($daysToCritical <= 180) return 'MODERATE_DECLINE';
        if ($daysToCritical <= 365) return 'SLOW_DECLINE';

        return 'STABLE';
    }

    protected function getRecommendation(string $deteriorationLevel, ?int $daysToCritical, float $currentHealth): string
    {
        return match($deteriorationLevel) {
            'CRITICAL_NOW' => 'IMMEDIATE ACTION REQUIRED: Component health is critical. Schedule emergency inspection.',
            'CRITICAL_IMMINENT' => "URGENT: Critical threshold expected in {$daysToCritical} days. Schedule maintenance immediately.",
            'RAPID_DECLINE' => "HIGH PRIORITY: Schedule maintenance within {$daysToCritical} days. Component deteriorating rapidly.",
            'MODERATE_DECLINE' => 'MONITOR CLOSELY: Include in next scheduled maintenance window.',
            'SLOW_DECLINE' => 'ROUTINE: Continue normal monitoring schedule.',
            'STABLE' => 'HEALTHY: No action required.',
            default => 'Continue monitoring.',
        };
    }

    /**
     * ============================================
     * PENALTY CALCULATION (USING DATABASE THRESHOLDS)
     * ============================================
     */
    protected function calculatePenaltyFromThreshold(float $value, ?array $threshold, float $maxPenalty, string $badDirection): float
    {
        if (!$threshold) {
            return 0; // No threshold defined, no penalty
        }

        $penalty = 0;

        if ($badDirection === 'high_is_bad' || $badDirection === 'both_bad') {
            // Check how far into "bad" territory we are
            $normalMax = $threshold['normal_max'] ?? null;
            $warningMax = $threshold['warning_max'] ?? null;
            $criticalMax = $threshold['critical_max'] ?? null;
            $failedMax = $threshold['failed_max'] ?? null;

            if ($failedMax && $value >= $failedMax) {
                $penalty = max($penalty, $maxPenalty);
            } elseif ($criticalMax && $value >= $criticalMax) {
                $penalty = max($penalty, $maxPenalty * 0.85);
            } elseif ($warningMax && $value >= $warningMax) {
                // Interpolate between warning and critical
                if ($criticalMax && $criticalMax > $warningMax) {
                    $ratio = ($value - $warningMax) / ($criticalMax - $warningMax);
                    $penalty = max($penalty, $maxPenalty * (0.5 + 0.35 * min(1, $ratio)));
                } else {
                    $penalty = max($penalty, $maxPenalty * 0.5);
                }
            } elseif ($normalMax && $value >= $normalMax) {
                // Interpolate between normal and warning
                if ($warningMax && $warningMax > $normalMax) {
                    $ratio = ($value - $normalMax) / ($warningMax - $normalMax);
                    $penalty = max($penalty, $maxPenalty * (0.1 + 0.4 * min(1, $ratio)));
                } else {
                    $penalty = max($penalty, $maxPenalty * 0.1);
                }
            }
        }

        if ($badDirection === 'low_is_bad' || $badDirection === 'both_bad') {
            $normalMin = $threshold['normal_min'] ?? null;
            $warningMin = $threshold['warning_min'] ?? null;
            $criticalMin = $threshold['critical_min'] ?? null;
            $failedMin = $threshold['failed_min'] ?? null;

            if ($failedMin !== null && $failedMin > 0 && $value <= $failedMin) {
                $penalty = max($penalty, $maxPenalty);
            } elseif ($criticalMin !== null && $criticalMin > 0 && $value <= $criticalMin) {
                $penalty = max($penalty, $maxPenalty * 0.85);
            } elseif ($warningMin !== null && $warningMin > 0 && $value <= $warningMin) {
                if ($criticalMin && $warningMin > $criticalMin) {
                    $ratio = ($warningMin - $value) / ($warningMin - $criticalMin);
                    $penalty = max($penalty, $maxPenalty * (0.5 + 0.35 * min(1, $ratio)));
                } else {
                    $penalty = max($penalty, $maxPenalty * 0.5);
                }
            } elseif ($normalMin !== null && $normalMin > 0 && $value <= $normalMin) {
                if ($warningMin && $normalMin > $warningMin) {
                    $ratio = ($normalMin - $value) / ($normalMin - $warningMin);
                    $penalty = max($penalty, $maxPenalty * (0.1 + 0.4 * min(1, $ratio)));
                } else {
                    $penalty = max($penalty, $maxPenalty * 0.1);
                }
            }
        }

        return min($maxPenalty, $penalty);
    }

    /**
     * ============================================
     * HELPER METHODS
     * ============================================
     */

    protected function getTrendConfidence(float $rSquared): string
    {
        return match(true) {
            $rSquared >= 0.8 => 'HIGH',
            $rSquared >= 0.5 => 'MEDIUM',
            $rSquared >= 0.3 => 'LOW',
            default => 'VERY_LOW',
        };
    }

    protected function getHealthStatus(float $score): string
    {
        return match(true) {
            $score >= 80 => 'EXCELLENT',
            $score >= 70 => 'GOOD',
            $score >= 50 => 'FAIR',
            $score >= 30 => 'POOR',
            default => 'CRITICAL',
        };
    }

    protected function calculateAlarmPenalty(int $turbineId, string $component, int $daysBack): float
    {
        $alarms = Alarm::where('turbine_id', $turbineId)
            ->where('component', 'LIKE', "%{$component}%")
            ->where('detected_at', '>=', Carbon::now()->subDays($daysBack))
            ->get();

        $penalty = 0;

        foreach ($alarms as $alarm) {
            $daysAgo = Carbon::parse($alarm->detected_at)->diffInDays(now());

            $severityWeight = match($alarm->severity) {
                'critical' => 15,
                'high' => 10,
                'medium' => 5,
                'low' => 2,
                default => 1,
            };

            $recencyWeight = match(true) {
                $daysAgo <= 30 => 1.0,
                $daysAgo <= 90 => 0.7,
                $daysAgo <= 180 => 0.4,
                default => 0.2,
            };

            $resolutionWeight = $alarm->status === 'resolved' ? 1.0 : 2.0;

            $penalty += $severityWeight * $recencyWeight * $resolutionWeight;
        }

        return min(30, $penalty);
    }

    protected function calculateBladeImbalancePenalty(int $turbineId, int $bladeNumber): float
    {
        $vibration = VibrationReading::where('turbine_id', $turbineId)
            ->latest('reading_timestamp')
            ->first();

        if (!$vibration) return 0;

        $blade1 = $vibration->blade1_vibration_mms;
        $blade2 = $vibration->blade2_vibration_mms;
        $blade3 = $vibration->blade3_vibration_mms;

        $mean = ($blade1 + $blade2 + $blade3) / 3;
        $variance = (pow($blade1 - $mean, 2) + pow($blade2 - $mean, 2) + pow($blade3 - $mean, 2)) / 3;
        $stdDev = sqrt($variance);

        // Higher penalty for larger standard deviation (imbalance)
        return min(10, $stdDev * 2);
    }

    protected function createEmptyHealthData(string $component): array
    {
        return [
            'component' => $component,
            'health_score' => null,
            'status' => 'NO_DATA',
            'current_data' => null,
            'penalties' => null,
            'sensor_trends' => null,
            'deterioration_analysis' => [
                'can_analyze' => false,
                'reason' => 'No sensor data available',
            ],
        ];
    }

    /**
     * Overall turbine health
     */
    public function calculateOverallHealth(int $turbineId, int $daysBack): array
    {
        $components = [
            'main_bearing' => $this->calculateMainBearingHealthSmart($turbineId, $daysBack),
            'gearbox' => $this->calculateGearboxHealthSmart($turbineId, $daysBack),
            'generator' => $this->calculateGeneratorHealthSmart($turbineId, $daysBack),
            'hydraulic_system' => $this->calculateHydraulicHealthSmart($turbineId, $daysBack),
            'tower' => $this->calculateTowerHealthSmart($turbineId, $daysBack),
        ];

        $weights = [
            'main_bearing' => 0.25,
            'gearbox' => 0.25,
            'generator' => 0.20,
            'hydraulic_system' => 0.20,
            'tower' => 0.10,
        ];

        $totalHealth = 0;
        $totalWeight = 0;
        $criticalComponents = [];
        $soonestCritical = null;

        foreach ($components as $name => $data) {
            if ($data['health_score'] !== null) {
                $totalHealth += $data['health_score'] * $weights[$name];
                $totalWeight += $weights[$name];

                if ($data['health_score'] < 50) {
                    $criticalComponents[] = [
                        'component' => $name,
                        'health_score' => $data['health_score'],
                        'status' => $data['status'],
                        'days_to_critical' => $data['deterioration_analysis']['predictions']['days_to_critical'] ?? null,
                    ];
                }

                // Track soonest critical prediction
                $daysToCritical = $data['deterioration_analysis']['predictions']['days_to_critical'] ?? null;
                if ($daysToCritical !== null && ($soonestCritical === null || $daysToCritical < $soonestCritical)) {
                    $soonestCritical = $daysToCritical;
                }
            }
        }

        $overallHealth = $totalWeight > 0 ? $totalHealth / $totalWeight : 0;

        return [
            'overall_health_score' => round($overallHealth, 1),
            'status' => $this->getHealthStatus($overallHealth),
            'critical_components' => $criticalComponents,
            'soonest_critical_days' => $soonestCritical,
            'recommendation' => $this->getOverallRecommendation($overallHealth, $criticalComponents, $soonestCritical),
        ];
    }

    protected function getOverallRecommendation(float $health, array $criticalComponents, ?int $soonestCritical): string
    {
        if (!empty($criticalComponents)) {
            $count = count($criticalComponents);
            return "ATTENTION: {$count} component(s) in critical/poor condition. Immediate inspection recommended.";
        }

        if ($soonestCritical !== null && $soonestCritical <= 90) {
            return "SCHEDULE MAINTENANCE: Component(s) predicted to reach critical in {$soonestCritical} days.";
        }

        if ($health >= 80) {
            return "HEALTHY: All components operating normally. Continue routine monitoring.";
        }

        if ($health >= 60) {
            return "GOOD: Minor issues detected. Include in next scheduled maintenance.";
        }

        return "MONITOR: Multiple components showing elevated readings. Consider early inspection.";
    }
}
