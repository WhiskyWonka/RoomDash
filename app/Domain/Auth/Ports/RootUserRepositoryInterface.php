<?php

declare(strict_types=1);

namespace Domain\Auth\Ports;

use Domain\Auth\Entities\RootUser;

interface RootUserRepositoryInterface
{
    public function findById(string $id): ?RootUser;

    public function findByEmail(string $email): ?RootUser;

    public function countActive(): int;

    public function existsByEmail(string $email): bool;

    public function existsByUsername(string $username): bool;

    public function create(array $data): RootUser;

    public function update(string $id, array $data): RootUser;

    public function delete(string $id): void;

    public function clearEmailVerification(string $id): void;

    public function verifyEmail(string $id, string $hashedPassword): void;

    public function verifyPassword(string $email, string $password): ?RootUser;

    public function getTwoFactorSecret(string $id): ?string;

    public function setTwoFactorSecret(string $id, string $secret): void;

    public function enableTwoFactor(string $id): void;

    public function disableTwoFactor(string $id): void;

    public function getRecoveryCodes(string $id): array;

    public function setRecoveryCodes(string $id, array $codes): void;

    public function useRecoveryCode(string $id, string $code): bool;
}
