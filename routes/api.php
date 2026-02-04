<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

// Central API routes - restricted to central domains only
foreach (config('tenancy.central_domains', ['localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/health', HealthController::class);
        Route::apiResource('tenants', TenantController::class);
    });
}
