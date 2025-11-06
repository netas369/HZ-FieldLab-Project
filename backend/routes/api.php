<?php

use App\Http\Controllers\Api\LiveDataController;
use App\Http\Controllers\Api\TurbineController;
use App\Http\Controllers\Api\HistoricDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

Route::get('turbines', [TurbineController::class, 'all_turbines']);

Route::get('turbine/{turbineId}/MonthScadaData', [HistoricDataController::class, 'LogScadaMonthlyChanges']);

Route::get('turbine/{turbineId}/latestScadaData', [LiveDataController::class, 'getTurbineLatestScadaData']);

Route::get('turbine/{turbineId}/latestHydraulicReadings', [LiveDataController::class, 'getTurbineLatestHydraulicData']);

Route::get('turbine/{turbineId}/vibrations', [LiveDataController::class, 'getTurbineLatestVibrationReadings']);


