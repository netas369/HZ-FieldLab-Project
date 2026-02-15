<?php

use App\Http\Controllers\Api\AlarmController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ComponentHealthController;
use App\Http\Controllers\Api\HistoryDataController;
use App\Http\Controllers\Api\LiveDataController;
use App\Http\Controllers\api\SettingsController;
use App\Http\Controllers\Api\ThresholdController;
use App\Http\Controllers\Api\TurbineController;
use App\Http\Controllers\Api\DataImportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// ============================================
// LIVE DATA
// ============================================

Route::get('turbines', [TurbineController::class, 'all_turbines']);

Route::get('turbine/{turbineId}/latestScadaData', [LiveDataController::class, 'getScadaData']);

Route::get('turbine/{turbineId}/latestHydraulicReadings', [LiveDataController::class, 'getHydraulicData']);

Route::get('turbine/{turbineId}/vibrations', [LiveDataController::class, 'getVibrationsData']);

Route::get('turbine/{turbineId}/latestTemperatures', [LiveDataController::class, 'getTemperatureData']);

Route::get('turbine/{turbineId}/alarms', [LiveDataController::class, 'getAlarmsData']);

// Alarm status update (requires authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::patch('turbines/{turbineId}/alarms/{alarmId}', [AlarmController::class, 'updateStatus']);
});

// ============================================
// MAINTENANCE ROUTES
// ============================================
Route::middleware(['auth:sanctum'])->group(function () {
    // CRUD routes
    Route::get('maintenances', [MaintenanceController::class, 'index']);
    Route::get('maintenances/my-tasks', [MaintenanceController::class, 'myTasks']);
    Route::get('maintenances/{id}', [MaintenanceController::class, 'show']);
    Route::post('maintenances', [MaintenanceController::class, 'store']);
    Route::put('maintenances/{id}', [MaintenanceController::class, 'update']);
    Route::delete('maintenances/{id}', [MaintenanceController::class, 'destroy']);

    // Turbine-specific maintenance
    Route::get('turbines/{turbineId}/maintenances', [MaintenanceController::class, 'forTurbine']);

    // Create maintenance from alarm
    Route::post('alarms/{alarmId}/maintenance', [MaintenanceController::class, 'createFromAlarm']);
});

Route::get('dashboard/all', [LiveDataController::class, 'getAllTurbinesData']);
Route::get('analytics', [LiveDataController::class, 'getAnalyticsData']);

// ============================================
// HISTORY DATA
// ============================================

Route::get('turbine/historicalScadaData', [HistoryDataController::class, 'loadScadaDataBetweenTwoPeriods']);
Route::get('turbine/historicalHydraulicData', [HistoryDataController::class, 'loadHydraulicDataBetweenTwoPeriods']);
Route::get('turbine/historicalVibrationData', [HistoryDataController::class, 'loadVibrationDataBetweenTwoPeriods']);
Route::get('turbine/historicalTemperatureData', [HistoryDataController::class, 'loadTemperatureDataBetweenTwoPeriods']);
Route::get('turbine/allHistoricalData', [HistoryDataController::class, 'loadAllHistoricalDataBetweenTwoPeriods']);


// ============================================
// COMPONENT HEALTH ROUTES (Smart Analysis)
// ============================================
// Uses database thresholds, linear regression trends,
// R² confidence scoring, and predictive analysis

// NEW: Check data availability - understand what analysis is possible
Route::get('/turbines/{turbineId}/data-availability',
    [ComponentHealthController::class, 'getDataAvailability']);

// Get all component health data for a specific turbine
Route::get('/turbines/{turbineId}/component-health',
    [ComponentHealthController::class, 'getTurbineComponentHealth']);

// Get health summary for all turbines
Route::get('/turbines/component-health/summary',
    [ComponentHealthController::class, 'getAllTurbinesHealthSummary']);

// Get specific component health for a turbine
Route::get('/turbines/{turbineId}/component-health/{componentName}',
    [ComponentHealthController::class, 'getSpecificComponentHealth']);

// Get health for specific component across all turbines
Route::get('/component-health/{componentName}',
    [ComponentHealthController::class, 'getComponentHealthAcrossTurbines'])
    ->where('componentName', '^(?!attention-required).*$'); // Exclude attention-required

// NEW: Components needing attention (sorted by urgency)
Route::get('/component-health/attention-required',
    [ComponentHealthController::class, 'getComponentsNeedingAttention']);

// Get deterioration trends for a single turbine
Route::get('/turbines/{turbineId}/deterioration-trends',
    [ComponentHealthController::class, 'getDeteriorationTrends']);

// NEW: Get deterioration trends across ALL turbines
Route::get('/deterioration-trends',
    [ComponentHealthController::class, 'getAllDeteriorationTrends']);


// ============================================
// DATA Import Routes
// ============================================
    Route::post('/data-import', [DataImportController::class, 'import']);
    Route::post('/data-import/preflight', [DataImportController::class, 'preflight']);
    Route::post('/data-import/chunked/init', [DataImportController::class, 'initChunkedImport']);
    Route::post('/data-import/chunked/process', [DataImportController::class, 'processChunk']);
    Route::get('/data-import/chunked/status/{importId}', [DataImportController::class, 'getChunkedStatus']);
    Route::delete('/data-import/chunked/{importId}', [DataImportController::class, 'cancelChunkedImport']);



// ============================================
// Settings routes
// ============================================
Route::middleware(['auth:sanctum'])->group(function () {
Route::post('/settings/delete-data', [SettingsController::class, 'deleteAllData']);
});

// ============================================
// THRESHOLD MANAGEMENT ROUTES (TEMP: No auth for testing)
// ============================================
// Keep these protected
Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/thresholds/{id}', [ThresholdController::class, 'update']);
    Route::post('/thresholds/{id}/reset', [ThresholdController::class, 'reset']);
    Route::post('/thresholds/{id}/test', [ThresholdController::class, 'testValue']);
    Route::get('/thresholds', [ThresholdController::class, 'index']);
    Route::get('/thresholds/type/{type}', [ThresholdController::class, 'byType']);
    Route::get('/thresholds/{id}', [ThresholdController::class, 'show']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
        'role' => $request->user()->role,
    ]);
});

// Get list of users (for assignment dropdowns)
Route::middleware('auth:sanctum')->get('/users', function () {
    return response()->json(
        \App\Models\User::select('id', 'name', 'email', 'role')->get()
    );
});

// ============================================
// AUTH ROUTES (included here for /api prefix)
// ============================================
require __DIR__.'/auth.php';
