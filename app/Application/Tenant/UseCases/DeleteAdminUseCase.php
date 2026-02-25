<?php

declare(strict_types=1);

namespace Application\Tenant\UseCases;

use Domain\Tenant\Ports\TenantRepositoryInterface;
use Domain\User\Exceptions\UserNotFoundException;

class DeleteAdminUseCase
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
    ) {}

    public function execute(string $tenantId): bool
    {
        $user = $this->tenants->findAdminUser($tenantId);

        if (! $user) {
            throw new UserNotFoundException("Admin user not found for tenant ID: {$tenantId}");
        }

        $this->tenants->deleteAdminUser($tenantId, $user->id);

        return true;
    }
}
