<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle.after:3,10');
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle.after:3,60');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
    });

    Route::prefix('email')->group(function () {
        Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->name('verification.verify')
            ->middleware('signed');

        Route::post('/resend', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle.after:1,3');
    });

    Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])
        ->middleware('throttle:1,3');

    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:3,60');
});
