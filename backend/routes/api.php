<?php

use App\Http\Controllers\Api\AlarmController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ComponentHealthController;
use App\Http\Controllers\Api\HistoryDataController;
use App\Http\Controllers\Api\LiveDataController;
use App\Http\Controllers\api\SettingsController;
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

// ============================================
// HISTORY DATA
// ============================================

Route::get('turbine/historicalScadaData', [HistoryDataController::class, 'loadScadaDataBetweenTwoPeriods']);
Route::get('turbine/historicalHydraulicData', [HistoryDataController::class, 'loadHydraulicDataBetweenTwoPeriods']);
Route::get('turbine/historicalVibrationData', [HistoryDataController::class, 'loadVibrationDataBetweenTwoPeriods']);
Route::get('turbine/historicalTemperatureData', [HistoryDataController::class, 'loadTemperatureDataBetweenTwoPeriods']);
Route::get('turbine/allHistoricalData', [HistoryDataController::class, 'loadAllHistoricalDataBetweenTwoPeriods']);


// ============================================
// COMPONENT HEALTH ROUTES
// ============================================

// Get all component health data for a specific turbine
Route::get('/turbines/{turbineId}/component-health', [ComponentHealthController::class, 'getTurbineComponentHealth']);

// Get health summary for all turbines
Route::get('/turbines/component-health/summary', [ComponentHealthController::class, 'getAllTurbinesHealthSummary']);

// Get specific component health for a turbine
Route::get('/turbines/{turbineId}/component-health/{componentName}', [ComponentHealthController::class, 'getSpecificComponentHealth']);

// Get health for specific component across all turbines
Route::get('/component-health/{componentName}', [ComponentHealthController::class, 'getComponentHealthAcrossTurbines']);

// Get deterioration trends for a turbine
Route::get('/turbines/{turbineId}/deterioration-trends', [ComponentHealthController::class, 'getDeteriorationTrends']);


// ============================================
// DATA Import Routes
// ============================================
Route::middleware(['auth:sanctum', 'role:admin, data_analyst'])->group(function () {
    Route::post('/data-import', [DataImportController::class, 'import']);
    Route::post('/data-import/preflight', [DataImportController::class, 'preflight']);
    Route::post('/data-import/chunked/init', [DataImportController::class, 'initChunkedImport']);
    Route::post('/data-import/chunked/process', [DataImportController::class, 'processChunk']);
    Route::get('/data-import/chunked/status/{importId}', [DataImportController::class, 'getChunkedStatus']);
    Route::delete('/data-import/chunked/{importId}', [DataImportController::class, 'cancelChunkedImport']);
});


// ============================================
// Settings routes
// ============================================
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
Route::post('/settings/delete-data', [SettingsController::class, 'deleteAllData']);
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
