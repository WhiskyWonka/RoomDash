<?php

declare(strict_types=1);

namespace Application\Tenant\UseCases;

use Application\Tenant\DTOs\CreateAdminDTO;
use Domain\Auth\Entities\User;
use Domain\Tenant\Exceptions\TenantNotFoundException;
use Domain\Tenant\Ports\TenantRepositoryInterface;

class CreateAdminUseCase
{
    private const DEFAULT_PASSWORD = 'RooomDash123!';

    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
    ) {}

    public function execute(string $tenantId, CreateAdminDTO $data): User
    {
        $tenant = $this->tenants->findById($tenantId);

        if (! $tenant) {
            throw new TenantNotFoundException($tenantId);
        }

        return $this->tenants->createAdminUser(
            [
                'email' => $data->email,
                'password' => self::DEFAULT_PASSWORD,
                'username' => $data->username,
                'firstName' => $data->firstName,
                'lastName' => $data->lastName,
            ], $tenantId);
    }
}
