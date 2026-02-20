<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'require.2fa' => \App\Http\Middleware\Require2FA::class,
            'ensure.2fa.setup' => \App\Http\Middleware\EnsureTwoFactorSetup::class,
            'audit.log' => \App\Http\Middleware\AuditLogger::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Errores de Validación (FormRequests)
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        });

        // 2. Errores de Dominio (Tus excepciones personalizadas)
        // Ejemplo: DuplicateEmailException, InvalidTokenException
        $exceptions->render(function (\Domain\Auth\Exceptions\AlreadyVerifiedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Resource already verified',
            ], 400);
        });

        // 3. Error Genérico (Fallback de seguridad)
        $exceptions->render(function (\Throwable $e) {
            if (config('app.debug')) {
                return null;
            } // En dev dejamos que Laravel muestre el error real

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        });
    })->create();
