<?php

use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::apiResource('tenants', TenantController::class);
