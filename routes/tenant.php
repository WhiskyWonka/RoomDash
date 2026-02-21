<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'api',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'tenant' => tenant('id'),
    ]));

    Route::get('/public/info', function () {
        return response()->json([
            'status' => 'ok',
            'id' => tenant('id'),
            'name' => tenant('name'),
        ]);
    });
});
