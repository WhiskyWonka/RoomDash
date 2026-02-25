<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\TwoFactorController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Central API routes - restricted to central domains only
foreach (config('tenancy.central_domains', ['localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/health', HealthController::class);

        // Public auth routes with rate limiting
        Route::middleware(['web', 'throttle:login'])->group(function () {
            Route::post('/auth/login', [LoginController::class, 'login'])->middleware('audit.log:user,users');
            Route::post('/auth/verify-email', VerifyEmailController::class)->middleware('audit.log:user,users');
        });

        // Authenticated routes (no 2FA required yet)
        Route::middleware('web')->group(function () {
            Route::post('/auth/logout', [LoginController::class, 'logout'])->middleware('audit.log:user,users');
            Route::get('/auth/me', [LoginController::class, 'me']);

            // 2FA verification with rate limiting
            Route::middleware('throttle:2fa')->group(function () {
                Route::post('/auth/verify-2fa', [LoginController::class, 'verify2fa'])->middleware('audit.log:user,users');
                Route::post('/auth/verify-recovery', [LoginController::class, 'verifyRecoveryCode'])->middleware('audit.log:user,users');
                Route::post('/auth/2fa/confirm', [TwoFactorController::class, 'confirm'])->middleware('audit.log:user,users');
                Route::get('/auth/2fa/setup', [TwoFactorController::class, 'setup'])->middleware('audit.log:user,users');
                Route::get('/auth/2fa/status', [TwoFactorController::class, 'status']);
            });
        });

        // Protected routes (requires 2FA verification)
        Route::middleware(['web', 'require.2fa'])->group(function () {
            Route::apiResource('tenants', TenantController::class);
            Route::patch('/tenants/{id}/activate', [TenantController::class, 'activate']);
            Route::patch('/tenants/{id}/deactivate', [TenantController::class, 'deactivate']);

            // User CRUD
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
            Route::post('/users', [UserController::class, 'store'])->middleware('audit.log:user,users');
            Route::put('/users/{id}', [UserController::class, 'update'])->middleware('audit.log:user,users');
            Route::patch('/users/{id}/password', [UserController::class, 'changePassword'])->middleware('audit.log:user,users');
            Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('audit.log:user,users');

            // User Activation / Deactivation
            Route::patch('/users/{id}/deactivate', [UserController::class, 'deactivate'])->middleware('audit.log:user,users');
            Route::patch('/users/{id}/activate', [UserController::class, 'activate'])->middleware('audit.log:user,users');

            // User Resend Verification
            Route::post('/users/{id}/resend-verification', [UserController::class, 'resendVerification'])->middleware('audit.log:user,users');

            // User Avatar
            Route::post('/users/{id}/avatar', [UserController::class, 'uploadAvatar'])->middleware('audit.log:user,users');
            Route::delete('/users/{id}/avatar', [UserController::class, 'deleteAvatar'])->middleware('audit.log:user,users');

            // Audit Logs (read-only)
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);

            Route::post('/tenants/{tenantId}/create-admin', [TenantController::class, 'createTenantAdmin'])->middleware('audit.log:user,users');
            Route::get('/tenants/{tenantId}/admin', [TenantController::class, 'getAdmin']);
            Route::put('/tenants/{tenantId}/admin', [TenantController::class, 'updateTenantAdmin'])->middleware('audit.log:user,users');
            Route::delete('/tenants/{tenantId}/admin', [TenantController::class, 'deleteAdmin'])->middleware('audit.log:user,users');
            Route::post('/tenants/{tenantId}/admin/resend-verification', [TenantController::class, 'resendAdminVerification'])->middleware('audit.log:user,users');

            // Audit logs are immutable - return 405 for modification attempts
            Route::match(['put', 'patch', 'delete'], '/audit-logs/{id}', function () {
                return response()->json(['message' => 'Method not allowed'], 405);
            });
        });
    });
}
