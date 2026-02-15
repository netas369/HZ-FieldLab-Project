<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordResetController;

Route::post('/user/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('/user/forgot-password', [PasswordResetController::class, 'forgotPassword'])
    ->middleware('guest');

Route::post('/user/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->middleware('guest');

Route::post('/user/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('logout');
