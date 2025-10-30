<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportWindFarmData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:wind-farm-data {file : Path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import wind farm SCADA and CMS data from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $this->info('Starting import...');
        $startTime = microtime(true);

        // Open CSV file
        $file = fopen($filePath, 'r');

        // Read header row
        $headers = fgetcsv($file);

        // Count total rows for progress bar
        $totalRows = 0;
        while (fgets($file) !== false) {
            $totalRows++;
        }
        rewind($file);
        fgetcsv($file); // Skip header again

        $this->info("Total rows to import: {$totalRows}");

        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalRows);
        $progressBar->start();

        // Prepare batch arrays
        $turbineIds = [];
        $scadaBatch = [];
        $temperatureBatch = [];
        $vibrationBatch = [];
        $healthBatch = [];
        $gridBatch = [];
        $hydraulicBatch = [];

        $batchSize = 1000;
        $processedRows = 0;

        // Process CSV rows
        $skippedRows = 0;
        while (($row = fgetcsv($file)) !== false) {
            try {
                // Parse timestamp (format: 2024-10-20 00:00:00)
                $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $row[0]);
                $turbineIdString = $row[1];

                // Get or create turbine ID
                if (!isset($turbineIds[$turbineIdString])) {
                    $turbine = DB::table('turbines')
                        ->where('turbine_id', $turbineIdString)
                        ->first();

                    if (!$turbine) {
                        $turbineDbId = DB::table('turbines')->insertGetId([
                            'turbine_id' => $turbineIdString,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $turbineDbId = $turbine->id;
                    }

                    $turbineIds[$turbineIdString] = $turbineDbId;
                }

                $turbineDbId = $turbineIds[$turbineIdString];

                // SCADA readings
                $scadaBatch[] = [
                    'turbine_id' => $turbineDbId,
                    'reading_timestamp' => $timestamp,
                    'wind_speed_ms' => $this->sanitizeValue($row[2], 0),
                    'power_kw' => $this->sanitizeValue($row[3], 0),
                    'rotor_speed_rpm' => $this->sanitizeValue($row[4], 0),
                    'generator_speed_rpm' => $this->sanitizeValue($row[5], 0),
                    'pitch_angle_deg' => $this->sanitizeValue($row[6], 0),
                    'yaw_angle_deg' => $this->sanitizeValue($row[7], 0),
                    'nacelle_direction_deg' => $this->sanitizeValue($row[8], 0),
                    'ambient_temp_c' => $this->sanitizeValue($row[9], 0),
                    'wind_direction_deg' => $this->sanitizeValue($row[24], 0),
                    'status_code' => $this->sanitizeValue($row[25], 0),
                    'alarm_code' => $this->sanitizeValue($row[26], 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Temperature readings
                $temperatureBatch[] = [
                    'turbine_id' => $turbineDbId,
                    'reading_timestamp' => $timestamp,
                    'nacelle_temp_c' => $this->sanitizeValue($row[10], 0),
                    'gearbox_bearing_temp_c' => $this->sanitizeValue($row[11], 0),
                    'gearbox_oil_temp_c' => $this->sanitizeValue($row[12], 0),
                    'generator_bearing1_temp_c' => $this->sanitizeValue($row[14], 0),
                    'generator_bearing2_temp_c' => $this->sanitizeValue($row[15], 0),
                    'generator_stator_temp_c' => $this->sanitizeValue($row[16], 0),
                    'main_bearing_temp_c' => $this->sanitizeValue($row[17], 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Vibration readings
                $vibrationBatch[] = [
                    'turbine_id' => $turbineDbId,
                    'reading_timestamp' => $timestamp,
                    'main_bearing_vibration_rms_mms' => $this->sanitizeValue($row[27], 0),
                    'main_bearing_vibration_peak_mms' => $this->sanitizeValue($row[28], 0),
                    'gearbox_vibration_axial_mms' => $this->sanitizeValue($row[29], 0),
                    'gearbox_vibration_radial_mms' => $this->sanitizeValue($row[30], 0),
                    'generator_vibration_de_mms' => $this->sanitizeValue($row[31], 0),
                    'generator_vibration_nde_mms' => $this->sanitizeValue($row[32], 0),
                    'tower_vibration_fa_mms' => $this->sanitizeValue($row[33], 0),
                    'tower_vibration_ss_mms' => $this->sanitizeValue($row[34], 0),
                    'blade1_vibration_mms' => $this->sanitizeValue($row[35], 0),
                    'blade2_vibration_mms' => $this->sanitizeValue($row[36], 0),
                    'blade3_vibration_mms' => $this->sanitizeValue($row[37], 0),
                    'acoustic_level_db' => $this->sanitizeValue($row[38], 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Health metrics
                $healthBatch[] = [
                    'turbine_id' => $turbineDbId,
                    'reading_timestamp' => $timestamp,
                    'bearing_wear_index' => $this->sanitizeValue($row[39], 0),
                    'oil_quality_index' => $this->sanitizeValue($row[40], 0),
                    'generator_health_index' => $this->sanitizeValue($row[41], 0),
                    'overall_health_score' => $this->sanitizeValue($row[42], 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Grid electrical readings
                $gridBatch[] = [
                    'turbine_id' => $turbineDbId,
                    'reading_timestamp' => $timestamp,
                    'grid_voltage_v' => $this->sanitizeValue($row[19], 0),
                    'grid_current_a' => $this->sanitizeValue($row[20], 0),
                    'grid_frequency_hz' => $this->sanitizeValue($row[21], 0),
                    'grid_power_factor' => $this->sanitizeValue($row[22], 0),
                    'reactive_power_kvar' => $this->sanitizeValue($row[23], 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Hydraulic readings
                $hydraulicBatch[] = [
                    'turbine_id' => $turbineDbId,
                    'reading_timestamp' => $timestamp,
                    'gearbox_oil_pressure_bar' => $this->sanitizeValue($row[13], 0),
                    'hydraulic_pressure_bar' => $this->sanitizeValue($row[18], 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $processedRows++;

                // Insert in batches
                if ($processedRows % $batchSize === 0) {
                    try {
                        $this->insertBatches(
                            $scadaBatch,
                            $temperatureBatch,
                            $vibrationBatch,
                            $healthBatch,
                            $gridBatch,
                            $hydraulicBatch
                        );
                    } catch (\Exception $e) {
                        $this->warn("\nError inserting batch at row {$processedRows}: " . $e->getMessage());
                        $skippedRows += count($scadaBatch);
                    }

                    // Clear batches
                    $scadaBatch = [];
                    $temperatureBatch = [];
                    $vibrationBatch = [];
                    $healthBatch = [];
                    $gridBatch = [];
                    $hydraulicBatch = [];
                }

            } catch (\Exception $e) {
                $this->warn("\nError processing row {$processedRows}: " . $e->getMessage());
                $skippedRows++;
            }

            $progressBar->advance();
        }

        // Insert remaining rows
        if (!empty($scadaBatch)) {
            try {
                $this->insertBatches(
                    $scadaBatch,
                    $temperatureBatch,
                    $vibrationBatch,
                    $healthBatch,
                    $gridBatch,
                    $hydraulicBatch
                );
            } catch (\Exception $e) {
                $this->warn("\nError inserting final batch: " . $e->getMessage());
                $skippedRows += count($scadaBatch);
            }
        }

        $progressBar->finish();
        fclose($file);

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        $this->newLine(2);
        $this->info("Import completed successfully!");
        $this->info("Total rows imported: " . ($processedRows - $skippedRows));
        $this->info("Skipped rows: {$skippedRows}");
        $this->info("Execution time: {$executionTime} seconds");

        return Command::SUCCESS;
    }

    /**
     * Insert batches into database
     */
    private function insertBatches($scada, $temperature, $vibration, $health, $grid, $hydraulic)
    {
        DB::table('scada_readings')->insert($scada);
        DB::table('temperature_readings')->insert($temperature);
        DB::table('vibration_readings')->insert($vibration);
        DB::table('health_metrics')->insert($health);
        DB::table('grid_electrical_readings')->insert($grid);
        DB::table('hydraulic_readings')->insert($hydraulic);
    }

    /**
     * Convert empty string or invalid values to null or 0
     */
    private function sanitizeValue($value, $default = null)
    {
        if ($value === '' || $value === null || strtolower($value) === 'null') {
            return $default;
        }
        return $value;
    }
}
