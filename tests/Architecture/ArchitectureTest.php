<?php

uses()->group('architecture');

// 1. Regla de Oro: El Dominio es sagrado (Puro)
test('Domain should not depend on Application or Infrastructure')
    ->expect('Domain')
    ->not->toUse(['Application', 'Infrastructure', 'Illuminate', 'Http'])
    ->ignoring(['PHPUnit']);

// 2. Aplicación: Solo puede mirar hacia abajo (al Dominio)
test('Application should only depend on Domain or itself')
    ->expect('Application')
    ->not->toUse(['Infrastructure', 'Http', 'Illuminate'])
    ->ignoring(['PHPUnit']);

// // 3. Infraestructura: Puede usar todo, pero NADIE puede usarla a ella (excepto Providers)
// test('Infrastructure should not be used by Domain or Application')
//     ->expect('Infrastructure')
//     ->not->toBeUsed()
//     ->ignoring([
//         'App\Providers',
//         'Illuminate\Support\ServiceProvider',
//     ]);

// 4. NUEVA PRUEBA CRÍTICA: Controladores NO pueden usar Modelos de Eloquent
test('Controllers must not use Eloquent models directly')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'Infrastructure\Auth\Models',      // Bloquea todos los modelos de Auth
        'Infrastructure\Tenant\Models',    // Bloquea todos los modelos de Tenant
        'Infrastructure\\Models',          // Bloquea cualquier modelo en Infrastructure
        'Illuminate\Database\Eloquent\Model',
        'Illuminate\Database\Query\Builder',
        'Illuminate\Database\Eloquent\Builder',
    ]);

// // 5. NUEVA PRUEBA CRÍTICA: Controladores NO pueden usar Facades directamente
// test('Controllers must not use Facades')
//     ->expect('Http\Controllers')
//     ->not->toUse([
//         'Illuminate\Support\Facades\Hash',
//         'Illuminate\Support\Facades\Auth',
//         'Illuminate\Support\Facades\DB',
//         'Illuminate\Support\Facades\\',    // Bloquea TODAS las facades
//     ]);

// // 6. NUEVA PRUEBA: Controladores deben usar casos de uso o repositorios
// test('Controllers should only use Application layer for business logic')
//     ->expect('Http\Controllers')
//     ->not->toUse(['Infrastructure', 'Illuminate\Database\Eloquent'])
//     ->ignoring([
//         'Illuminate\Support\Facades\Log',    // Excepción para Log
//     ]);

// 7. Verificar que los repositorios NO devuelven arrays (para el caso de EloquentAuditLogRepository)
// test('Repositories must return domain entities or collections')
//     ->expect('Infrastructure')
//     ->classes()
//     ->filter(fn ($class) => str_contains($class->getName(), 'Repository'))
//     ->toImplement('Domain\Ports\RepositoryInterface')
//     ->each(function ($class) {
//         // Verificar métodos findPaginated no devuelven arrays
//         $methods = $class->getMethods();
//         foreach ($methods as $method) {
//             if (str_contains($method->getName(), 'findPaginated')) {
//                 // Esta es una verificación más compleja que requeriría reflexión avanzada
//                 // Por ahora, documentamos que es una violación manual
//             }
//         }
//     });

// 8. NUEVA PRUEBA: LoginController específicamente
// test('LoginController must use use cases, not direct Hash')
//     ->expect('Http\Controllers\LoginController')
//     ->not->toUse([
//         'Illuminate\Support\Facades\Hash',
//         'Infrastructure\Auth\Models',
//     ]);

// 9. NUEVA PRUEBA: Require2FA middleware no debe usar Auth::user() directamente
// test('Middleware should use repository pattern')
//     ->expect('Http\Middleware')
//     ->not->toUse([
//         'Illuminate\Support\Facades\Auth',
//         'Infrastructure\Auth\Models',
//     ]);

// 10. Verificar que los casos de uso existen (los que menciona el análisis)
// test('Required use cases should exist for auth operations')
//     ->expect('Application\UseCases\Auth')
//     ->classes()
//     ->toHaveCount(4)  // ActivateUserUseCase, DeactivateUserUseCase, UploadAvatarUseCase, DeleteAvatarUseCase
//     ->ignoring('Application\UseCases\Auth\LoginUseCase');
