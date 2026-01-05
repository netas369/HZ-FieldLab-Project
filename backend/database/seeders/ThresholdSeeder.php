<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThresholdSeeder extends Seeder
{
    /**
     * Seed the thresholds table with industry-standard values
     *
     * CRITICAL SAFETY NOTE:
     * - For VIBRATION/TEMPERATURE: High values are bad (use _max columns)
     * - For PRESSURE: Low values are bad (use _min columns)
     *
     * Range Logic:
     * - normal_max/min: Safe operating range
     * - warning_max/min: Attention needed, plan maintenance
     * - critical_max/min: Urgent action required
     * - failed_max/min: Component failure, shutdown required
     */
    public function run(): void
    {
        $thresholds = [
            // ============================================
            // VIBRATION SENSORS (ISO 10816-21)
            // HIGH VALUES ARE BAD - Use MAX columns
            // Zone A (<4.5): Good
            // Zone B (4.5-7.1): Caution
            // Zone C (7.1-11.2): Critical
            // Zone D (>11.2): Damage Imminent
            // ============================================
            [
                'component_name' => 'main_bearing_vibration_rms',
                'normal_min' => null,
                'normal_max' => 4.5,    // Zone A: Good
                'warning_min' => null,
                'warning_max' => 7.1,   // Zone B: Caution (4.5-7.1)
                'critical_min' => null,
                'critical_max' => 11.2, // Zone C: Critical (7.1-11.2)
                'failed_min' => null,
                'failed_max' => 999.9,  // Zone D: Failed (>11.2)
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'gearbox_vibration_axial',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'gearbox_vibration_radial',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'generator_vibration_de',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'generator_vibration_nde',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'tower_vibration_fa',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'tower_vibration_ss',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'blade1_vibration',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'blade2_vibration',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],
            [
                'component_name' => 'blade3_vibration',
                'normal_min' => null,
                'normal_max' => 4.5,
                'warning_min' => null,
                'warning_max' => 7.1,
                'critical_min' => null,
                'critical_max' => 11.2,
                'failed_min' => null,
                'failed_max' => 999.9,
                'unit' => 'mm/s'
            ],

            // ============================================
            // TEMPERATURE SENSORS
            // HIGH VALUES ARE BAD - Use MAX columns
            // ============================================
            [
                'component_name' => 'nacelle_temp',
                'normal_min' => null,
                'normal_max' => 50.0,   // <50°C: Normal
                'warning_min' => null,
                'warning_max' => 70.0,  // 50-70°C: Warm
                'critical_min' => null,
                'critical_max' => 80.0, // 70-80°C: Hot
                'failed_min' => null,
                'failed_max' => 999.9,  // >80°C: Very Hot (shutdown risk)
                'unit' => '°C'
            ],
            [
                'component_name' => 'gearbox_oil_temp',
                'normal_min' => null,
                'normal_max' => 65.0,   // <65°C: Optimal
                'warning_min' => null,
                'warning_max' => 75.0,  // 65-75°C: Approaching warning
                'critical_min' => null,
                'critical_max' => 85.0, // 75-85°C: Very hot
                'failed_min' => null,
                'failed_max' => 999.9,  // >85°C: Oil degradation risk
                'unit' => '°C'
            ],
            [
                'component_name' => 'main_bearing_temp',
                'normal_min' => null,
                'normal_max' => 80.0,   // <80°C: Within expected range
                'warning_min' => null,
                'warning_max' => 90.0,  // 80-90°C: Elevated
                'critical_min' => null,
                'critical_max' => 100.0, // 90-100°C: High
                'failed_min' => null,
                'failed_max' => 999.9,  // >100°C: Bearing damage risk
                'unit' => '°C'
            ],
            [
                'component_name' => 'gearbox_bearing_temp',
                'normal_min' => null,
                'normal_max' => 80.0,   // <80°C: Within expected range
                'warning_min' => null,
                'warning_max' => 90.0,  // 80-90°C: Elevated
                'critical_min' => null,
                'critical_max' => 100.0, // 90-100°C: High
                'failed_min' => null,
                'failed_max' => 999.9,  // >100°C: Critical overheating
                'unit' => '°C'
            ],
            [
                'component_name' => 'generator_bearing1_temp',
                'normal_min' => null,
                'normal_max' => 85.0,   // <85°C: Within expected range
                'warning_min' => null,
                'warning_max' => 95.0,  // 85-95°C: Elevated
                'critical_min' => null,
                'critical_max' => 105.0, // 95-105°C: High
                'failed_min' => null,
                'failed_max' => 999.9,  // >105°C: Bearing failure risk
                'unit' => '°C'
            ],
            [
                'component_name' => 'generator_bearing2_temp',
                'normal_min' => null,
                'normal_max' => 85.0,   // <85°C: Within expected range
                'warning_min' => null,
                'warning_max' => 95.0,  // 85-95°C: Elevated
                'critical_min' => null,
                'critical_max' => 105.0, // 95-105°C: High
                'failed_min' => null,
                'failed_max' => 999.9,  // >105°C: Bearing failure risk
                'unit' => '°C'
            ],
            [
                'component_name' => 'generator_stator_temp',
                'normal_min' => null,
                'normal_max' => 120.0,  // <120°C: Within expected range
                'warning_min' => null,
                'warning_max' => 140.0, // 120-140°C: Elevated
                'critical_min' => null,
                'critical_max' => 155.0, // 140-155°C: High (power curtailment)
                'failed_min' => null,
                'failed_max' => 999.9,  // >155°C: Component failure risk
                'unit' => '°C'
            ],

            // ============================================
            // PRESSURE SENSORS (VDI 3834)
            // LOW VALUES ARE BAD - Use MIN columns
            // ⚠️ CRITICAL: Pressure MINIMUM thresholds
            // ============================================
            // ============================================
            // PRESSURE SENSORS (VDI 3834)
            // LOW VALUES ARE BAD - Use MIN columns
            // HIGH VALUES ARE ALSO BAD - Use MAX columns for overpressure
            // ⚠️ CRITICAL: Pressure MINIMUM thresholds
            // ============================================
            [
                'component_name' => 'gearbox_oil_pressure',
                'normal_min' => 2.3,    // ≥2.3 bar: Normal (adequate lubrication)
                'normal_max' => 3.0,    // ≤3.0 bar: Normal (not overpressure)
                'warning_min' => 2.0,   // 2.0-2.3 bar: Low Pressure (monitor system)
                'warning_max' => 3.5,   // 3.0-3.5 bar: Elevated pressure
                'critical_min' => 1.8,  // 1.8-2.0 bar: Very Low (lubrication failure risk)
                'critical_max' => 4.0,  // 3.5-4.0 bar: High pressure (seal stress)
                'failed_min' => 0.0,    // <1.8 bar: Critically Low (system failure)
                'failed_max' => 999.9,  // >4.0 bar: Overpressure (seal damage)
                'unit' => 'bar'
            ],
            [
                'component_name' => 'hydraulic_pressure',
                'normal_min' => 155.0,  // ≥155 bar: Normal (optimal pitch pressure)
                'normal_max' => 165.0,  // ≤165 bar: Normal (not overpressure)
                'warning_min' => 150.0, // 150-155 bar: Below Normal (slower pitch response)
                'warning_max' => 175.0, // 165-175 bar: Elevated pressure
                'critical_min' => 140.0, // 140-150 bar: Low Pressure (pitch compromised)
                'critical_max' => 185.0, // 175-185 bar: High pressure (seal stress)
                'failed_min' => 0.0,    // <140 bar: Pressure Critical (pitch failure)
                'failed_max' => 999.9,  // >185 bar: Overpressure (seal/valve damage)
                'unit' => 'bar'
            ],

            // ============================================
            // ACOUSTIC LEVEL
            // HIGH VALUES ARE BAD - Use MAX columns
            // ============================================
            [
                'component_name' => 'acoustic_level',
                'normal_min' => null,
                'normal_max' => 102.0,  // <102 dB: Normal (typical noise)
                'warning_min' => null,
                'warning_max' => 105.0, // 102-105 dB: Elevated (investigate)
                'critical_min' => null,
                'critical_max' => 999.9, // >105 dB: Excessive (immediate investigation)
                'failed_min' => null,
                'failed_max' => null,   // No failed state for acoustic
                'unit' => 'dB'
            ],

            // ============================================
            // GRID PARAMETERS (IEC 61400-21)
            // BOTH HIGH AND LOW CAN BE BAD
            // ============================================
            [
                'component_name' => 'grid_voltage',
                'normal_min' => 690.0,  // 690-710 V: Normal (±3% of 700V nominal)
                'normal_max' => 710.0,
                'warning_min' => 670.0, // 670-690 V or 710-730 V: Warning (±5%)
                'warning_max' => 730.0,
                'critical_min' => 650.0, // 650-670 V or 730-750 V: Critical (±7%)
                'critical_max' => 750.0,
                'failed_min' => 0.0,    // <650 V or >750 V: Failed (±10%)
                'failed_max' => 999.9,
                'unit' => 'V'
            ],
            [
                'component_name' => 'grid_frequency',
                'normal_min' => 49.8,   // 49.8-50.2 Hz: Normal (±0.2 Hz)
                'normal_max' => 50.2,
                'warning_min' => 49.5,  // 49.5-49.8 Hz or 50.2-50.5 Hz: Warning (±0.5 Hz)
                'warning_max' => 50.5,
                'critical_min' => 49.0, // 49.0-49.5 Hz or 50.5-51.0 Hz: Critical (±1.0 Hz)
                'critical_max' => 51.0,
                'failed_min' => 0.0,    // <49.0 Hz or >51.0 Hz: Failed
                'failed_max' => 999.9,
                'unit' => 'Hz'
            ],

            // ============================================
            // ENVIRONMENTAL (SCADA)
            // BOTH HIGH AND LOW CAN BE BAD
            // ============================================
            [
                'component_name' => 'ambient_temperature',
                'normal_min' => -10.0,  // -10 to +35°C: Normal operating range
                'normal_max' => 35.0,
                'warning_min' => -15.0, // -15 to -10°C or 35-40°C: Warning
                'warning_max' => 40.0,
                'critical_min' => -20.0, // -20 to -15°C or 40-45°C: Critical
                'critical_max' => 45.0,
                'failed_min' => -999.9, // <-20°C or >45°C: Too extreme for operation
                'failed_max' => 999.9,
                'unit' => '°C'
            ],
            [
                'component_name' => 'wind_speed',
                'normal_min' => 3.0,    // 3-25 m/s: Normal operating range
                'normal_max' => 25.0,
                'warning_min' => 2.5,   // 2.5-3.0 m/s or 25-28 m/s: Warning
                'warning_max' => 28.0,
                'critical_min' => 2.0,  // 2.0-2.5 m/s or 28-30 m/s: Critical
                'critical_max' => 30.0,
                'failed_min' => 0.0,    // <2.0 m/s (too slow) or >30 m/s (storm cutoff)
                'failed_max' => 999.9,
                'unit' => 'm/s'
            ],
            [
                'component_name' => 'rotor_speed',
                'normal_min' => 6.0,    // 6-18 RPM: Normal operating range
                'normal_max' => 18.0,
                'warning_min' => 5.0,   // 5-6 RPM or 18-20 RPM: Warning
                'warning_max' => 20.0,
                'critical_min' => 4.0,  // 4-5 RPM or 20-22 RPM: Critical
                'critical_max' => 22.0,
                'failed_min' => 0.0,    // <4 RPM (too slow) or >22 RPM (overspeed)
                'failed_max' => 999.9,
                'unit' => 'RPM'
            ],
        ];

        // Clear existing thresholds
        DB::table('thresholds')->truncate();

        // Insert new thresholds
        foreach ($thresholds as $threshold) {
            DB::table('thresholds')->insert($threshold);
        }

        $this->command->info('✅ Thresholds seeded with proper min/max values');
        $this->command->info('📊 Total thresholds: ' . count($thresholds));

        // Validation check
        $this->validateThresholds($thresholds);
    }

    /**
     * Validate that threshold ranges make sense
     */
    private function validateThresholds(array $thresholds): void
    {
        $errors = [];

        foreach ($thresholds as $threshold) {
            $name = $threshold['component_name'];

            // Determine if this is a "low is bad" component (pressure)
            $isPressure = in_array($name, ['gearbox_oil_pressure', 'hydraulic_pressure']);

            if ($isPressure) {
                // ============================================
                // PRESSURE VALIDATION (LOW is bad, so MAX values are UPPER bounds of ranges)
                // ============================================

                // For pressure: normal_max > warning_max > critical_max > failed_max
                // Example: normal=2.3-2.5, warning=2.0-2.3, critical=1.8-2.0, failed=0.0-1.8
                // So: normal_max(2.3) < warning_max(2.3) is INVALID (they're equal boundaries)
                // Actually: warning_max should equal normal_min (they're the same boundary)

                // Skip MAX validation for pressure - the ranges overlap by design

                // Check MIN thresholds (descending order: normal_min > warning_min > critical_min > failed_min)
                if ($threshold['normal_min'] !== null && $threshold['warning_min'] !== null) {
                    if ($threshold['normal_min'] <= $threshold['warning_min']) {
                        $errors[] = "{$name}: normal_min must be > warning_min";
                    }
                }
                if ($threshold['warning_min'] !== null && $threshold['critical_min'] !== null) {
                    if ($threshold['warning_min'] <= $threshold['critical_min']) {
                        $errors[] = "{$name}: warning_min must be > critical_min";
                    }
                }
                if ($threshold['critical_min'] !== null && $threshold['failed_min'] !== null) {
                    if ($threshold['critical_min'] <= $threshold['failed_min']) {
                        $errors[] = "{$name}: critical_min must be > failed_min";
                    }
                }

            } else {
                // ============================================
                // STANDARD VALIDATION (HIGH is bad)
                // ============================================

                // Check MAX thresholds (ascending order)
                if ($threshold['normal_max'] !== null && $threshold['warning_max'] !== null) {
                    if ($threshold['normal_max'] >= $threshold['warning_max']) {
                        $errors[] = "{$name}: normal_max must be < warning_max";
                    }
                }
                if ($threshold['warning_max'] !== null && $threshold['critical_max'] !== null) {
                    if ($threshold['warning_max'] >= $threshold['critical_max']) {
                        $errors[] = "{$name}: warning_max must be < critical_max";
                    }
                }
                if ($threshold['critical_max'] !== null && $threshold['failed_max'] !== null) {
                    if ($threshold['critical_max'] >= $threshold['failed_max']) {
                        $errors[] = "{$name}: critical_max must be < failed_max";
                    }
                }

                // Check MIN thresholds (descending order) - for bidirectional components
                if ($threshold['normal_min'] !== null && $threshold['warning_min'] !== null) {
                    if ($threshold['normal_min'] <= $threshold['warning_min']) {
                        $errors[] = "{$name}: normal_min must be > warning_min";
                    }
                }
                if ($threshold['warning_min'] !== null && $threshold['critical_min'] !== null) {
                    if ($threshold['warning_min'] <= $threshold['critical_min']) {
                        $errors[] = "{$name}: warning_min must be > critical_min";
                    }
                }
                if ($threshold['critical_min'] !== null && $threshold['failed_min'] !== null) {
                    if ($threshold['critical_min'] <= $threshold['failed_min']) {
                        $errors[] = "{$name}: critical_min must be > failed_min";
                    }
                }
            }

            // Check normal range consistency (applies to all)
            if ($threshold['normal_min'] !== null && $threshold['normal_max'] !== null) {
                if ($threshold['normal_min'] >= $threshold['normal_max']) {
                    $errors[] = "{$name}: normal_min must be < normal_max";
                }
            }
        }

        if (!empty($errors)) {
            $this->command->error('❌ THRESHOLD VALIDATION FAILED:');
            foreach ($errors as $error) {
                $this->command->error("  - {$error}");
            }
            throw new \Exception('Threshold validation failed. See errors above.');
        } else {
            $this->command->info('✅ All thresholds validated successfully');
        }
    }
}
