<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Turbine;
use App\Enums\TurbineStatus; // <--- 1. IMPORT ADDED
use Carbon\Carbon;

class DataImportService
{
    // Chunk size for batch processing
    const CHUNK_SIZE = 1000;

    // Table field mappings
    const TABLE_FIELDS = [
        'vibration_readings' => [
            'main_bearing_vibration_rms_mms',
            'main_bearing_vibration_peak_mms',
            'gearbox_vibration_axial_mms',
            'gearbox_vibration_radial_mms',
            'generator_vibration_de_mms',
            'generator_vibration_nde_mms',
            'tower_vibration_fa_mms',
            'tower_vibration_ss_mms',
            'blade1_vibration_mms',
            'blade2_vibration_mms',
            'blade3_vibration_mms',
            'acoustic_level_db'
        ],
        'temperature_readings' => [
            'nacelle_temp_c',
            'gearbox_bearing_temp_c',
            'gearbox_oil_temp_c',
            'generator_bearing1_temp_c',
            'generator_bearing2_temp_c',
            'generator_stator_temp_c',
            'main_bearing_temp_c'
        ],
        'scada_readings' => [
            'wind_speed_ms',
            'power_kw',
            'rotor_speed_rpm',
            'generator_speed_rpm',
            'pitch_angle_deg',
            'yaw_angle_deg',
            'nacelle_direction_deg',
            'ambient_temp_c',
            'wind_direction_deg',
            'status_code',
            'alarm_code'
        ],
        'hydraulic_readings' => [
            'gearbox_oil_pressure_bar',
            'hydraulic_pressure_bar'
        ],
        'grid_electrical_readings' => [
            'grid_voltage_v',
            'grid_current_a',
            'grid_frequency_hz',
            'grid_power_factor',
            'reactive_power_kvar'
        ]
    ];

    /**
     * Main import processor
     */
    public function processImport($keyColumns, $sensorMapping, $data, $fileName)
    {
        $stats = [
            'total_rows' => count($data),
            'imported_rows' => 0,
            'skipped_rows' => 0,
            'error_rows' => 0,
            'created_turbines' => [],
            'table_counts' => [
                'vibration' => 0,
                'temperature' => 0,
                'scada' => 0,
                'hydraulic' => 0,
                'grid' => 0
            ],
            'errors' => []
        ];

        try {
            // Step 1: Validate and prepare turbines
            $turbineMap = $this->prepareTurbines($keyColumns, $data, $stats);

            // Step 2: Process data in chunks
            $chunks = array_chunk($data, self::CHUNK_SIZE);

            foreach ($chunks as $chunkIndex => $chunk) {
                try {
                    DB::beginTransaction();

                    $this->processChunk(
                        $chunk,
                        $keyColumns,
                        $sensorMapping,
                        $turbineMap,
                        $stats
                    );

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollback();
                    Log::error("Chunk {$chunkIndex} failed", [
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            $stats['turbines_count'] = count($turbineMap);

            return $stats;

        } catch (\Exception $e) {
            Log::error('Import process failed', [
                'error' => $e->getMessage(),
                'file' => $fileName
            ]);
            throw $e;
        }
    }

    /**
     * Prepare turbines - validate existing, create new ones
     */
    protected function prepareTurbines($keyColumns, $data, &$stats)
    {
        $turbineMap = [];

        // Get mode safely
        $mode = $keyColumns['turbineIdMode'] ?? null;

        if ($mode === 'single') {
            // Single turbine mode
            $turbineId = $keyColumns['singleTurbineId'] ?? null;

            if (empty($turbineId)) {
                throw new \Exception("Single turbine ID is required when mode is 'single'");
            }

            $turbine = Turbine::where('turbine_id', $turbineId)->first();

            if (!$turbine) {
                throw new \Exception("Turbine {$turbineId} not found in database");
            }

            $turbineMap[$turbineId] = $turbine->id;

        } elseif ($mode === 'column') {
            // Multiple turbines from CSV column
            $turbineColumn = $keyColumns['turbineColumn'] ?? null;

            if (empty($turbineColumn)) {
                throw new \Exception("Turbine column is required when mode is 'column'");
            }

            $uniqueTurbineIds = array_unique(array_column($data, $turbineColumn));

            foreach ($uniqueTurbineIds as $turbineId) {
                if (empty($turbineId)) continue;

                $turbine = Turbine::where('turbine_id', $turbineId)->first();

                if (!$turbine) {
                    // Auto-create new turbine
                    $turbine = Turbine::create([
                        'turbine_id' => $turbineId,
                        'status' => TurbineStatus::Normal // <--- 2. FIXED: Use Enum instead of integer 1
                    ]);

                    $stats['created_turbines'][] = $turbineId;

                    Log::info("Auto-created turbine: {$turbineId}");
                }

                $turbineMap[$turbineId] = $turbine->id;
            }
        } else {
            throw new \Exception("Invalid turbineIdMode: {$mode}");
        }

        return $turbineMap;
    }

    /**
     * Process a chunk of rows
     */
    protected function processChunk($chunk, $keyColumns, $sensorMapping, $turbineMap, &$stats)
    {
        $mode = $keyColumns['turbineIdMode'] ?? null;

        foreach ($chunk as $rowIndex => $row) {
            try {
                // Get turbine ID based on mode
                if ($mode === 'single') {
                    $turbineIdValue = $keyColumns['singleTurbineId'] ?? null;
                } else {
                    $turbineColumn = $keyColumns['turbineColumn'] ?? null;
                    $turbineIdValue = !empty($turbineColumn) ? ($row[$turbineColumn] ?? null) : null;
                }

                if (empty($turbineIdValue) || !isset($turbineMap[$turbineIdValue])) {
                    $stats['error_rows']++;
                    $stats['errors'][] = [
                        'row' => $rowIndex + 1,
                        'message' => 'Invalid turbine ID'
                    ];
                    continue;
                }

                $turbineId = $turbineMap[$turbineIdValue];

                // Parse timestamp
                $timestampColumn = $keyColumns['timestampColumn'] ?? null;

                if (empty($timestampColumn)) {
                    $stats['error_rows']++;
                    $stats['errors'][] = [
                        'row' => $rowIndex + 1,
                        'message' => 'Timestamp column not specified'
                    ];
                    continue;
                }

                $timestamp = $this->parseTimestamp($row[$timestampColumn] ?? null);

                if (!$timestamp) {
                    $stats['error_rows']++;
                    $stats['errors'][] = [
                        'row' => $rowIndex + 1,
                        'message' => 'Invalid timestamp'
                    ];
                    continue;
                }

                // Check for duplicate
                if ($this->isDuplicate($turbineId, $timestamp)) {
                    $stats['skipped_rows']++;
                    continue;
                }

                // Insert into each table
                $inserted = $this->insertToTables(
                    $turbineId,
                    $timestamp,
                    $row,
                    $sensorMapping,
                    $stats
                );

                if ($inserted) {
                    $stats['imported_rows']++;
                } else {
                    $stats['skipped_rows']++;
                }

            } catch (\Exception $e) {
                $stats['error_rows']++;
                $stats['errors'][] = [
                    'row' => $rowIndex + 1,
                    'message' => $e->getMessage()
                ];
                Log::warning("Row {$rowIndex} failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Insert data into appropriate tables
     */
    protected function insertToTables($turbineId, $timestamp, $row, $sensorMapping, &$stats)
    {
        $insertedAny = false;

        foreach (self::TABLE_FIELDS as $table => $fields) {
            $tableData = $this->extractTableData($row, $fields, $sensorMapping);

            // Only insert if we have at least one non-NULL value
            if (!empty($tableData)) {
                $tableData['turbine_id'] = $turbineId;
                $tableData['reading_timestamp'] = $timestamp;

                DB::table($table)->insert($tableData);

                // Update stats
                $tableKey = str_replace('_readings', '', $table);
                if (isset($stats['table_counts'][$tableKey])) {
                    $stats['table_counts'][$tableKey]++;
                }

                $insertedAny = true;
            }
        }

        return $insertedAny;
    }

    /**
     * Extract data for a specific table
     */
    protected function extractTableData($row, $fields, $sensorMapping)
    {
        $data = [];

        foreach ($fields as $field) {
            if (isset($sensorMapping[$field]) && !empty($sensorMapping[$field])) {
                $csvColumn = $sensorMapping[$field];
                $value = $row[$csvColumn] ?? null;

                // Convert value
                $convertedValue = $this->convertValue($value);

                if ($convertedValue !== null) {
                    $data[$field] = $convertedValue;
                }
            }
        }

        return $data;
    }

    /**
     * Convert CSV value to appropriate type
     */
    protected function convertValue($value)
    {
        // Handle empty values
        if ($value === null || $value === '' || $value === 'NULL') {
            return null;
        }

        // Handle common "no data" strings
        $noDataStrings = ['N/A', 'n/a', 'NA', 'ERROR', 'ERR', '-', '#N/A'];
        if (in_array($value, $noDataStrings, true)) {
            return null;
        }

        // Try to convert to number
        if (is_numeric($value)) {
            return floatval($value);
        }

        return $value;
    }

    /**
     * Parse timestamp from various formats
     */
    protected function parseTimestamp($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $timestamp = Carbon::parse($value);

            // Validate: timestamp should be between 2000 and 2100
            if ($timestamp->year < 2000 || $timestamp->year > 2100) {
                return null;
            }

            return $timestamp->format('Y-m-d H:i:s');

        } catch (\Exception $e) {
            Log::warning("Failed to parse timestamp: {$value}");
            return null;
        }
    }

    /**
     * Check if record already exists
     */
    protected function isDuplicate($turbineId, $timestamp)
    {
        // Check in any of the sensor tables
        $exists = DB::table('vibration_readings')
            ->where('turbine_id', $turbineId)
            ->where('reading_timestamp', $timestamp)
            ->exists();

        return $exists;
    }

    /**
     * Analyze import before processing (pre-flight check)
     */
    public function analyzeImport($keyColumns, $data)
    {
        $analysis = [
            'total_rows' => count($data),
            'unique_turbines' => [],
            'timestamp_range' => [
                'start' => null,
                'end' => null
            ],
            'duplicate_count' => 0,
            'new_turbines' => []
        ];

        $mode = $keyColumns['turbineIdMode'] ?? null;

        // Extract turbine IDs
        if ($mode === 'single') {
            $turbineId = $keyColumns['singleTurbineId'] ?? null;
            if ($turbineId) {
                $analysis['unique_turbines'] = [$turbineId];
            }
        } else {
            $turbineColumn = $keyColumns['turbineColumn'] ?? null;
            if (!empty($turbineColumn)) {
                $analysis['unique_turbines'] = array_unique(
                    array_filter(array_column($data, $turbineColumn))
                );
            }
        }

        // Find timestamp range
        $timestamps = [];
        $timestampColumn = $keyColumns['timestampColumn'] ?? null;

        if (!empty($timestampColumn)) {
            foreach ($data as $row) {
                $ts = $this->parseTimestamp($row[$timestampColumn] ?? null);
                if ($ts) {
                    $timestamps[] = $ts;
                }
            }

            if (!empty($timestamps)) {
                sort($timestamps);
                $analysis['timestamp_range']['start'] = reset($timestamps);
                $analysis['timestamp_range']['end'] = end($timestamps);
            }
        }

        // Check for new turbines
        foreach ($analysis['unique_turbines'] as $turbineId) {
            $exists = Turbine::where('turbine_id', $turbineId)->exists();
            if (!$exists) {
                $analysis['new_turbines'][] = $turbineId;
            }
        }

        // Check for duplicates (sample check - first 100 rows)
        $sampleData = array_slice($data, 0, 100);
        foreach ($sampleData as $row) {
            if ($mode === 'single') {
                $turbineIdValue = $keyColumns['singleTurbineId'] ?? null;
            } else {
                $turbineColumn = $keyColumns['turbineColumn'] ?? null;
                $turbineIdValue = !empty($turbineColumn) ? ($row[$turbineColumn] ?? null) : null;
            }

            if ($turbineIdValue) {
                $turbine = Turbine::where('turbine_id', $turbineIdValue)->first();
                if ($turbine && !empty($timestampColumn)) {
                    $timestamp = $this->parseTimestamp($row[$timestampColumn] ?? null);
                    if ($timestamp && $this->isDuplicate($turbine->id, $timestamp)) {
                        $analysis['duplicate_count']++;
                    }
                }
            }
        }

        return $analysis;
    }
}
