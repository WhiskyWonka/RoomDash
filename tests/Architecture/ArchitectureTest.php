<?php

uses()->group('architecture');

// ─────────────────────────────────────────────
// Regla 1: El Domain es puro — no depende de nada externo
// ─────────────────────────────────────────────
arch('Domain must not depend on Application, Infrastructure or Http')
    ->expect('Domain')
    ->not->toUse(['Application', 'Infrastructure', 'Illuminate', 'App\\Http']);

// ─────────────────────────────────────────────
// Regla 2: Application solo mira hacia el Domain (por módulo para facilitar diagnóstico)
// VIOLA: Application\User\UseCases\CreateUserUseCase importa App\Http\Controllers\Api\Concerns\ApiResponse
// ─────────────────────────────────────────────
arch('Application\User must not depend on Infrastructure or Http')
    ->expect('Application\User')
    ->not->toUse(['Infrastructure', 'App\\Http', 'Illuminate\\Http']);

arch('Application\AuditLog must not depend on Infrastructure or Http')
    ->expect('Application\AuditLog')
    ->not->toUse(['Infrastructure', 'App\\Http', 'Illuminate\\Http']);

arch('Application\EmailVerification must not depend on Infrastructure or Http')
    ->expect('Application\EmailVerification')
    ->not->toUse(['Infrastructure', 'App\\Http', 'Illuminate\\Http']);

arch('Application\Login must not depend on Infrastructure or Http')
    ->expect('Application\Login')
    ->not->toUse(['Infrastructure', 'App\\Http', 'Illuminate\\Http']);

arch('Application\Tenant must not depend on Infrastructure or Http')
    ->expect('Application\Tenant')
    ->not->toUse(['Infrastructure', 'App\\Http', 'Illuminate\\Http']);

// ─────────────────────────────────────────────
// Regla 3: Controllers nunca usan modelos Eloquent directamente
// ─────────────────────────────────────────────
arch('Controllers must not use Eloquent models directly')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'Infrastructure\Auth\Models',
        'Infrastructure\Tenant\Models',
        'Infrastructure\AuditLog\Models',
        'Illuminate\Database\Eloquent\Model',
        'Illuminate\Database\Query\Builder',
        'Illuminate\Database\Eloquent\Builder',
    ]);

// ─────────────────────────────────────────────
// Regla 4: Controllers inyectan interfaces (Ports), nunca adaptadores concretos (por área)
// VIOLA: LoginController inyecta LaravelPasswordHasher (Infrastructure\Shared\Adapters)
// ─────────────────────────────────────────────
arch('Auth controllers must inject interfaces, not concrete adapters')
    ->expect('App\Http\Controllers\Api\Auth')
    ->not->toUse([
        'Infrastructure\Auth\Adapters',
        'Infrastructure\Tenant\Adapters',
        'Infrastructure\AuditLog\Adapters',
        'Infrastructure\Shared\Adapters',
    ]);

arch('Api controllers must inject interfaces, not concrete adapters')
    ->expect('App\Http\Controllers\Api\UserController')
    ->not->toUse([
        'Infrastructure\Auth\Adapters',
        'Infrastructure\Tenant\Adapters',
        'Infrastructure\AuditLog\Adapters',
        'Infrastructure\Shared\Adapters',
    ]);

arch('Tenant controller must inject interfaces, not concrete adapters')
    ->expect('App\Http\Controllers\Api\TenantController')
    ->not->toUse([
        'Infrastructure\Auth\Adapters',
        'Infrastructure\Tenant\Adapters',
        'Infrastructure\AuditLog\Adapters',
        'Infrastructure\Shared\Adapters',
    ]);

// ─────────────────────────────────────────────
// Regla 5: Las Entities del Domain deben ser final (inmutables)
// ─────────────────────────────────────────────
arch('Domain entities must be final')
    ->expect('Domain\*\Entities')
    ->toBeFinal();

// ─────────────────────────────────────────────
// Regla 6: El Domain usa DateTimeImmutable, nunca Carbon
// ─────────────────────────────────────────────
arch('Domain must not use Carbon')
    ->expect('Domain')
    ->not->toUse(['Carbon\Carbon', 'Illuminate\Support\Carbon']);

// ─────────────────────────────────────────────
// Regla 7: Los Ports del Domain deben ser interfaces
// ─────────────────────────────────────────────
arch('Domain ports must be interfaces')
    ->expect('Domain\*\Ports')
    ->toBeInterfaces();

// ─────────────────────────────────────────────
// Regla 8: Controllers usan FormRequests — nunca validan con Request directo (por área)
// Excepción permitida: usar Request para session(), ip(), userAgent() sin validar
// ─────────────────────────────────────────────
arch('Auth controllers must use FormRequests, not raw Request')
    ->expect('App\Http\Controllers\Api\Auth')
    ->not->toUse('Illuminate\Http\Request')
    ->ignoring([
        'App\Http\Controllers\Api\Auth\LoginController',      // usa session() para 2FA state — sin validación inline
        'App\Http\Controllers\Api\Auth\TwoFactorController',  // usa session() para 2FA state — sin validación inline
    ]);

arch('Api controllers must use FormRequests, not raw Request')
    ->expect('App\Http\Controllers\Api\UserController')
    ->not->toUse('Illuminate\Http\Request')
    ->ignoring([
        'App\Http\Controllers\Api\UserController', // usa session(), ip(), userAgent() — sin validación inline
    ]);

arch('Tenant controller must use FormRequests, not raw Request')
    ->expect('App\Http\Controllers\Api\TenantController')
    ->not->toUse('Illuminate\Http\Request');

// ─────────────────────────────────────────────
// Regla 9: Los modelos Eloquent solo pueden ser usados dentro de Infrastructure
// ─────────────────────────────────────────────
arch('Infrastructure models must only be used within Infrastructure')
    ->expect('Infrastructure\*\Models')
    ->toOnlyBeUsedIn('Infrastructure')
    ->ignoring(['Tests', 'Database\Factories']);
