<?php

use App\Http\Controllers\Api\HistoryDataController;
use App\Http\Controllers\Api\LiveDataController;
use App\Http\Controllers\Api\TurbineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// LIVE DATA
Route::get('turbines', [TurbineController::class, 'all_turbines']);

Route::get('turbine/{turbineId}/latestScadaData', [LiveDataController::class, 'getScadaData']);

Route::get('turbine/{turbineId}/latestHydraulicReadings', [LiveDataController::class, 'getHydraulicData']);

Route::get('turbine/{turbineId}/vibrations', [LiveDataController::class, 'getVibrationsData']);

Route::get('turbine/{turbineId}/latestTemperatures', [LiveDataController::class, 'getTemperatureData']);

Route::get('turbine/{turbineId}/alarms', [LiveDataController::class, 'getAlarmsData']);

Route::get('dashboard/all', [LiveDataController::class, 'getAllTurbinesData']);

// HISTORY DATA
Route::get('turbine/historicalScadaData', [HistoryDataController::class, 'loadScadaDataBetweenTwoPeriods']);
Route::get('turbine/historicalHydraulicData', [HistoryDataController::class, 'loadHydraulicDataBetweenTwoPeriods']);
Route::get('turbine/historicalVibrationData', [HistoryDataController::class, 'loadVibrationDataBetweenTwoPeriods']);
Route::get('turbine/historicalTemperatureData', [HistoryDataController::class, 'loadTemperatureDataBetweenTwoPeriods']);
