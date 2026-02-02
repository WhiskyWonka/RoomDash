<?php

declare(strict_types=1);

namespace Infrastructure\Tenant\Providers;

use Domain\Tenant\Ports\TenantRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Tenant\Adapters\Persistence\EloquentTenantRepository;

class TenantServiceProvider extends ServiceProvider
{
    public array $bindings = [
        TenantRepositoryInterface::class => EloquentTenantRepository::class,
    ];
}
