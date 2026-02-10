<?php

declare(strict_types=1);

namespace Domain\Auth\Ports;

use Domain\Auth\Entities\AdminUser;

interface AdminUserRepositoryInterface
{
    public function findById(string $id): ?AdminUser;

    public function findByEmail(string $email): ?AdminUser;

    public function verifyPassword(string $email, string $password): ?AdminUser;

    public function getTwoFactorSecret(string $id): ?string;

    public function setTwoFactorSecret(string $id, string $secret): void;

    public function enableTwoFactor(string $id): void;

    public function disableTwoFactor(string $id): void;

    public function getRecoveryCodes(string $id): array;

    public function setRecoveryCodes(string $id, array $codes): void;

    public function useRecoveryCode(string $id, string $code): bool;
}
