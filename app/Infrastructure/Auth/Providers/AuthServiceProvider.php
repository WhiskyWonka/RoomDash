<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Providers;

use Domain\Auth\Ports\AdminUserRepositoryInterface;
use Domain\Auth\Ports\TwoFactorServiceInterface;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Auth\Adapters\EloquentAdminUserRepository;
use Infrastructure\Auth\Adapters\Google2faService;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminUserRepositoryInterface::class, EloquentAdminUserRepository::class);
        $this->app->bind(TwoFactorServiceInterface::class, Google2faService::class);
    }

    public function boot(): void
    {
        //
    }
}
