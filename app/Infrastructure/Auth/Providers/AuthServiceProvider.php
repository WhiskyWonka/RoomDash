<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Providers;

use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Ports\AdminUserRepositoryInterface;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\Auth\Ports\EmailVerificationTokenRepositoryInterface;
use Domain\Auth\Ports\TwoFactorServiceInterface;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Auth\Services\LastActiveUserGuard;
use Illuminate\Support\ServiceProvider;
use Infrastructure\AuditLog\Adapters\EloquentAuditLogRepository;
use Infrastructure\Auth\Adapters\EloquentAdminUserRepository;
use Infrastructure\Auth\Adapters\EloquentEmailVerificationTokenRepository;
use Infrastructure\Auth\Adapters\EloquentUserRepository;
use Infrastructure\Auth\Adapters\EmailVerificationService;
use Infrastructure\Auth\Adapters\Google2faService;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Legacy binding (still used by existing controllers)
        $this->app->bind(AdminUserRepositoryInterface::class, EloquentAdminUserRepository::class);

        // User bindings
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(EmailVerificationServiceInterface::class, EmailVerificationService::class);
        $this->app->bind(EmailVerificationTokenRepositoryInterface::class, EloquentEmailVerificationTokenRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);

        $this->app->bind(TwoFactorServiceInterface::class, Google2faService::class);

        // Domain services
        $this->app->bind(LastActiveUserGuard::class, function ($app) {
            return new LastActiveUserGuard($app->make(UserRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
