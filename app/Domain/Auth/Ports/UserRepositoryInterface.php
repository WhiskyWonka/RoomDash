<?php

declare(strict_types=1);

namespace Domain\Auth\Ports;

use Domain\Auth\Entities\User;
use Domain\Shared\DTOs\PaginatedResult;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function countActive(): int;

    public function existsById(string $id): bool;

    public function existsByEmail(string $email): bool;

    public function existsByUsername(string $username): bool;

    public function create(array $data): User;

    public function update(string $id, array $data): User;

    public function delete(string $id): void;

    public function clearEmailVerification(string $id): void;

    public function verifyEmail(string $id, string $hashedPassword): void;

    public function verifyPassword(string $email, string $password): ?User;

    public function getTwoFactorSecret(string $id): ?string;

    public function setTwoFactorSecret(string $id, string $secret): void;

    public function enableTwoFactor(string $id): void;

    public function disableTwoFactor(string $id): void;

    public function getRecoveryCodes(string $id): array;

    public function setRecoveryCodes(string $id, array $codes): void;

    public function useRecoveryCode(string $id, string $code): bool;

    public function listPaginated(
        int $page,
        int $perPage,
        string $sortField,
        string $sortDirection
    ): PaginatedResult;
}
