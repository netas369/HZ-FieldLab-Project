<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThresholdSeeder extends Seeder
{
    public function run(): void
    {
        $thresholds = [
            // Vibration (ISO 10816)
            ['component_name' => 'main_bearing_vibration_rms', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'gearbox_vibration_axial', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'gearbox_vibration_radial', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'generator_vibration_de', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'generator_vibration_nde', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'tower_vibration_fa', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'tower_vibration_ss', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'blade1_vibration', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'blade2_vibration', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],
            ['component_name' => 'blade3_vibration', 'normal_max' => 4.5, 'warning_max' => 7.1, 'critical_max' => 11.2, 'failed_max' => null, 'unit' => 'mm/s'],

            // Temperature
            ['component_name' => 'nacelle_temp', 'normal_max' => 50.0, 'warning_max' => 70.0, 'critical_max' => 80.0, 'failed_max' => null, 'unit' => '°C'],
            ['component_name' => 'gearbox_oil_temp', 'normal_max' => 65.0, 'warning_max' => 75.0, 'critical_max' => 85.0, 'failed_max' => null, 'unit' => '°C'],
            ['component_name' => 'main_bearing_temp', 'normal_max' => 80.0, 'warning_max' => 90.0, 'critical_max' => 100.0, 'failed_max' => null, 'unit' => '°C'],
            ['component_name' => 'gearbox_bearing_temp', 'normal_max' => 80.0, 'warning_max' => 90.0, 'critical_max' => 100.0, 'failed_max' => null, 'unit' => '°C'],
            ['component_name' => 'generator_bearing1_temp', 'normal_max' => 85.0, 'warning_max' => 95.0, 'critical_max' => 105.0, 'failed_max' => null, 'unit' => '°C'],
            ['component_name' => 'generator_bearing2_temp', 'normal_max' => 85.0, 'warning_max' => 95.0, 'critical_max' => 105.0, 'failed_max' => null, 'unit' => '°C'],
            ['component_name' => 'generator_stator_temp', 'normal_max' => 120.0, 'warning_max' => 140.0, 'critical_max' => 155.0, 'failed_max' => null, 'unit' => '°C'],

            // Pressure (note: these are MINIMUM thresholds - low is bad!)
            ['component_name' => 'gearbox_oil_pressure', 'normal_max' => 2.3, 'warning_max' => 2.0, 'critical_max' => 1.8, 'failed_max' => null, 'unit' => 'bar'],
            ['component_name' => 'hydraulic_pressure', 'normal_max' => 155.0, 'warning_max' => 150.0, 'critical_max' => 140.0, 'failed_max' => null, 'unit' => 'bar'],

            // Acoustic
            ['component_name' => 'acoustic_level', 'normal_max' => 102.0, 'warning_max' => 105.0, 'critical_max' => null, 'failed_max' => null, 'unit' => 'dB'],

            // Grid
            ['component_name' => 'grid_voltage', 'normal_max' => 710.0, 'warning_max' => 730.0, 'critical_max' => 750.0, 'failed_max' => null, 'unit' => 'V'],
            ['component_name' => 'grid_frequency', 'normal_max' => 50.2, 'warning_max' => 50.5, 'critical_max' => 51.0, 'failed_max' => null, 'unit' => 'Hz'],
        ];

        DB::table('thresholds')->insert($thresholds);
    }
}
