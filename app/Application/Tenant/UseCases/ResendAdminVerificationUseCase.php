<?php

declare(strict_types=1);

namespace Application\Tenant\UseCases;

use Domain\Tenant\Exceptions\TenantNotFoundException;
use Domain\Tenant\Ports\TenantRepositoryInterface;
use Domain\User\Exceptions\UserNotFoundException;

class ResendAdminVerificationUseCase
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
    ) {}

    public function execute(string $tenantId): void
    {
        $tenant = $this->tenants->findById($tenantId);

        if (! $tenant) {
            throw new TenantNotFoundException($tenantId);
        }

        $user = $this->tenants->findAdminUser($tenantId);

        if (! $user) {
            throw new UserNotFoundException("Admin user not found for tenant ID: {$tenantId}");
        }

        $this->tenants->resendAdminVerification($tenantId);
    }
}
