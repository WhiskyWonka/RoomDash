<?php

declare(strict_types=1);

namespace Domain\Tenant\Ports;

use Application\Tenant\DTOs\CreateAdminDTO;
use Application\Tenant\DTOs\UpdateAdminDTO;
use Domain\Auth\Entities\User;
use Domain\Tenant\Entities\Tenant;

interface TenantRepositoryInterface
{
    public function findById(string $id): ?Tenant;

    public function findByDomain(string $domain): ?Tenant;

    public function create(string $name, string $domain): Tenant;

    public function findAll(): array;

    public function update(string $id, string $name, string $domain): Tenant;

    public function deactivate(string $id): void;

    public function activate(string $id): void;

    public function delete(string $id): void;

    public function createAdminUser(CreateAdminDTO $data, string $tenantId): User;

    public function findAdminUser(string $tenantId): ?User;

    public function updateAdminUser(string $tenantId, string $userId, UpdateAdminDTO $data): User;

    public function deleteAdminUser(string $tenantId, string $userId): void;
}
