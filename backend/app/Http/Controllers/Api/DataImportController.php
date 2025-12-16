<?php

namespace App\Http\Controllers\Api;  // ← Must have Api here!

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        ini_set('memory_limit', '1024M'); // Increase to 1GB
        // Log the raw request for debugging
        Log::info('Import request received', [
            'key_columns' => $request->input('key_columns')
        ]);

        // Validate request - FIXED: Added turbineColumn and singleTurbineId
        $validated = $request->validate([
            'key_columns' => 'required|array',
            'key_columns.turbineIdMode' => 'required|in:column,single',
            'key_columns.turbineColumn' => 'nullable|string',           // ← ADDED
            'key_columns.singleTurbineId' => 'nullable|string',         // ← ADDED
            'key_columns.timestampColumn' => 'required|string',
            'key_columns.uniqueTurbines' => 'nullable|array',           // ← ADDED
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
