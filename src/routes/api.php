<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Catalog\CatalogController;
use App\Http\Controllers\Profile\EmailChangeVerificationController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle.after:3,10');
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle.after:3,60');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);

        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::post('/change-email', [ProfileController::class, 'changeEmail']);
            Route::post('/change-password', [ProfileController::class, 'changePassword']);
        });
    });

    Route::get('profile/verify-email-change/{id}/{hash}', [EmailChangeVerificationController::class, 'verify'])
        ->name('profile.email-change.verify')
        ->middleware('signed');

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

    Route::prefix('catalog')->group(function () {
        Route::get('specializations', [CatalogController::class, 'specializations']);
        Route::get('specializations/{id}/languages', [CatalogController::class, 'specializationLanguages']);
    });
});
