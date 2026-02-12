<?php

declare(strict_types=1);

namespace Domain\Auth\Ports;

use Domain\Auth\ValueObjects\EmailVerificationToken;

interface EmailVerificationTokenRepositoryInterface
{
    public function findByHashedToken(string $hashedToken): ?EmailVerificationToken;

    public function deleteByUserId(string $userId): void;
}
