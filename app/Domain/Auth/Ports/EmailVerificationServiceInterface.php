<?php

declare(strict_types=1);

namespace Domain\Auth\Ports;

interface EmailVerificationServiceInterface
{
    public function sendVerificationEmail(string $userId): void;

    public function invalidatePreviousTokens(string $userId): void;
}
