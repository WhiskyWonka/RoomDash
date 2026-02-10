<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\TwoFactorController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

// Central API routes - restricted to central domains only
foreach (config('tenancy.central_domains', ['localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/health', HealthController::class);

        // Public auth routes with rate limiting
        Route::middleware(['web', 'throttle:login'])->group(function () {
            Route::post('/auth/login', [LoginController::class, 'login']);
        });

        // Authenticated routes (no 2FA required yet)
        Route::middleware('web')->group(function () {
            Route::post('/auth/logout', [LoginController::class, 'logout']);
            Route::get('/auth/me', [LoginController::class, 'me']);

            // 2FA verification with rate limiting
            Route::middleware('throttle:2fa')->group(function () {
                Route::post('/auth/verify-2fa', [LoginController::class, 'verify2fa']);
                Route::post('/auth/verify-recovery', [LoginController::class, 'verifyRecoveryCode']);
                Route::post('/auth/2fa/confirm', [TwoFactorController::class, 'confirm']);
            });

            Route::get('/auth/2fa/setup', [TwoFactorController::class, 'setup']);
            Route::get('/auth/2fa/status', [TwoFactorController::class, 'status']);
        });

        // Protected routes (requires 2FA verification)
        Route::middleware(['web', 'require.2fa'])->group(function () {
            Route::apiResource('tenants', TenantController::class);
        });
    });
}
