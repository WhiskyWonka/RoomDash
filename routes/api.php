<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\TwoFactorController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\RootUserController;
use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

// Central API routes - restricted to central domains only
foreach (config('tenancy.central_domains', ['localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/health', HealthController::class);

        // Public auth routes with rate limiting
        Route::middleware(['web', 'throttle:login'])->group(function () {
            Route::post('/auth/login', [LoginController::class, 'login']);
            Route::post('/auth/verify-email', VerifyEmailController::class);
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

            // Root User CRUD
            Route::get('/root-users', [RootUserController::class, 'index']);
            Route::get('/root-users/{id}', [RootUserController::class, 'show']);
            Route::post('/root-users', [RootUserController::class, 'store'])->middleware('audit.log');
            Route::put('/root-users/{id}', [RootUserController::class, 'update'])->middleware('audit.log');
            // TODO: agregar /root-users/{id}/password para cambiar contraseña sin afectar email o rol
            Route::delete('/root-users/{id}', [RootUserController::class, 'destroy']);

            // Root User Activation / Deactivation
            Route::patch('/root-users/{id}/deactivate', [RootUserController::class, 'deactivate']);
            Route::patch('/root-users/{id}/activate', [RootUserController::class, 'activate']);

            // Root User Resend Verification
            Route::post('/root-users/{id}/resend-verification', [RootUserController::class, 'resendVerification']);

            // Root User Avatar
            Route::post('/root-users/{id}/avatar', [RootUserController::class, 'uploadAvatar']);
            Route::delete('/root-users/{id}/avatar', [RootUserController::class, 'deleteAvatar']);

            // Audit Logs (read-only)
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);

            // Audit logs are immutable - return 405 for modification attempts
            Route::match(['put', 'patch', 'delete'], '/audit-logs/{id}', function () {
                return response()->json(['message' => 'Method not allowed'], 405);
            });
        });
    });
}
