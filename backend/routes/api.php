<?php

use App\Http\Controllers\Api\LiveDataController;
use App\Http\Controllers\Api\TurbineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

Route::get('turbines', [TurbineController::class, 'all_turbines']);

Route::get('turbine/{turbineId}/latest', [LiveDataController::class, 'getTurbineLatest']);
