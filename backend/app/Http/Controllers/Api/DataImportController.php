<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\DataImportService;
use Carbon\Carbon;

class DataImportController extends Controller
{
    protected $importService;

    public function __construct(DataImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Import CSV data into multiple sensor tables
     *
     * POST /api/data-import
     */
    public function import(Request $request)
    {
        // Increase limits for this request
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '300');
        ini_set('max_input_time', '300');

        // Log the raw request for debugging
        Log::info('Import request received', [
            'key_columns' => $request->input('key_columns')
        ]);

        // Validate request
        $validated = $request->validate([
            'key_columns' => 'required|array',
            'key_columns.turbineIdMode' => 'required|in:column,single',
            'key_columns.turbineColumn' => 'nullable|string',
            'key_columns.singleTurbineId' => 'nullable|string',
            'key_columns.timestampColumn' => 'required|string',
            'key_columns.uniqueTurbines' => 'nullable|array',
            'sensor_mapping' => 'required|array',
            'data' => 'required|array|min:1',
            'file_name' => 'nullable|string'
        ]);

        try {
            // Start timing
            $startTime = microtime(true);

            // Log what we're processing
            Log::info('Processing import', [
                'mode' => $validated['key_columns']['turbineIdMode'],
                'turbineColumn' => $validated['key_columns']['turbineColumn'] ?? 'null',
                'singleTurbineId' => $validated['key_columns']['singleTurbineId'] ?? 'null',
                'row_count' => count($validated['data'])
            ]);

            // Process import
            $result = $this->importService->processImport(
                $validated['key_columns'],
                $validated['sensor_mapping'],
                $validated['data'],
                $validated['file_name'] ?? 'unknown.csv'
            );

            // Calculate duration
            $duration = round(microtime(true) - $startTime, 2);
            $result['import_duration_seconds'] = $duration;

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                ...$result
            ], 200);

        } catch (\Exception $e) {
            Log::error('Data import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * CHUNKED IMPORT - Initialize import session
     *
     * POST /api/data-import/chunked/init
     */
    public function initChunkedImport(Request $request)
    {
        $validated = $request->validate([
            'key_columns' => 'required|array',
            'sensor_mapping' => 'required|array',
            'file_name' => 'required|string',
            'total_rows' => 'required|integer',
            'chunk_size' => 'required|integer'
        ]);

        $importId = uniqid('import_', true);

        // Store import session in cache (1 hour expiry)
        Cache::put("import_session_{$importId}", [
            'key_columns' => $validated['key_columns'],
            'sensor_mapping' => $validated['sensor_mapping'],
            'file_name' => $validated['file_name'],
            'total_rows' => $validated['total_rows'],
            'chunk_size' => $validated['chunk_size'],
            'processed_rows' => 0,
            'chunks_processed' => 0,
            'total_chunks' => ceil($validated['total_rows'] / $validated['chunk_size']),
            'started_at' => now(),
            'stats' => [
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
            ]
        ], 3600);

        return response()->json([
            'success' => true,
            'import_id' => $importId,
            'message' => 'Import session initialized'
        ]);
    }

    /**
     * CHUNKED IMPORT - Process a chunk
     *
     * POST /api/data-import/chunked/process
     */
    public function processChunk(Request $request)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '120');

        $validated = $request->validate([
            'import_id' => 'required|string',
            'chunk_number' => 'required|integer',
            'data' => 'required|array|min:1'
        ]);

        $importId = $validated['import_id'];
        $session = Cache::get("import_session_{$importId}");

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Import session not found or expired'
            ], 404);
        }

        try {
            // Process this chunk
            $result = $this->importService->processImport(
                $session['key_columns'],
                $session['sensor_mapping'],
                $validated['data'],
                $session['file_name']
            );

            // Merge stats
            $session['processed_rows'] += count($validated['data']);
            $session['chunks_processed']++;
            $session['stats']['imported_rows'] += $result['imported_rows'];
            $session['stats']['skipped_rows'] += $result['skipped_rows'];
            $session['stats']['error_rows'] += $result['error_rows'];

            // Merge created turbines (unique)
            $session['stats']['created_turbines'] = array_unique(
                array_merge($session['stats']['created_turbines'], $result['created_turbines'])
            );

            // Merge table counts
            foreach ($result['table_counts'] as $table => $count) {
                $session['stats']['table_counts'][$table] += $count;
            }

            // Merge errors (limit to 100 total)
            if (count($session['stats']['errors']) < 100) {
                $session['stats']['errors'] = array_merge(
                    $session['stats']['errors'],
                    array_slice($result['errors'], 0, 100 - count($session['stats']['errors']))
                );
            }

            // Update session
            Cache::put("import_session_{$importId}", $session, 3600);

            $isComplete = $session['chunks_processed'] >= $session['total_chunks'];

            return response()->json([
                'success' => true,
                'import_id' => $importId,
                'chunk_number' => $validated['chunk_number'],
                'processed_rows' => $session['processed_rows'],
                'total_rows' => $session['total_rows'],
                'progress_percent' => round(($session['processed_rows'] / $session['total_rows']) * 100, 2),
                'is_complete' => $isComplete,
                'stats' => $isComplete ? $session['stats'] : null
            ]);

        } catch (\Exception $e) {
            Log::error('Chunk processing failed', [
                'import_id' => $importId,
                'chunk_number' => $validated['chunk_number'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Chunk processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * CHUNKED IMPORT - Get status
     *
     * GET /api/data-import/chunked/status/{importId}
     */
    public function getChunkedStatus($importId)
    {
        $session = Cache::get("import_session_{$importId}");

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Import session not found'
            ], 404);
        }

        $isComplete = $session['chunks_processed'] >= $session['total_chunks'];

        return response()->json([
            'success' => true,
            'import_id' => $importId,
            'processed_rows' => $session['processed_rows'],
            'total_rows' => $session['total_rows'],
            'progress_percent' => round(($session['processed_rows'] / $session['total_rows']) * 100, 2),
            'chunks_processed' => $session['chunks_processed'],
            'total_chunks' => $session['total_chunks'],
            'is_complete' => $isComplete,
            'started_at' => $session['started_at'],
            'stats' => $isComplete ? $session['stats'] : null
        ]);
    }

    /**
     * CHUNKED IMPORT - Cancel/cleanup
     *
     * DELETE /api/data-import/chunked/{importId}
     */
    public function cancelChunkedImport($importId)
    {
        Cache::forget("import_session_{$importId}");

        return response()->json([
            'success' => true,
            'message' => 'Import session cancelled'
        ]);
    }

    /**
     * Pre-flight check for duplicates
     *
     * POST /api/data-import/preflight
     */
    public function preflight(Request $request)
    {
        $validated = $request->validate([
            'key_columns' => 'required|array',
            'data' => 'required|array|min:1'
        ]);

        try {
            $analysis = $this->importService->analyzeImport(
                $validated['key_columns'],
                $validated['data']
            );

            return response()->json([
                'success' => true,
                ...$analysis
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preflight check failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
