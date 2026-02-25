<?php

declare(strict_types=1);

namespace Application\Tenant\UseCases;

use Application\Tenant\DTOs\UpdateAdminDTO;
use Domain\Auth\Entities\User;
use Domain\Tenant\Ports\TenantRepositoryInterface;
use Domain\User\Exceptions\UserNotFoundException;

class UpdateAdminUseCase
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
    ) {}

    public function execute(string $tenantId, UpdateAdminDTO $data): User
    {
        $user = $this->tenants->findAdminUser($tenantId);

        if (! $user) {
            throw new UserNotFoundException("Admin user not found for tenant ID: {$tenantId}");
        }

        $user = $this->tenants->updateAdminUser($tenantId, $user->id,
            [
                'email' => $data->email,
                'username' => $data->username,
                'firstName' => $data->firstName,
                'lastName' => $data->lastName,
            ]);

        return $user;
    }
}
