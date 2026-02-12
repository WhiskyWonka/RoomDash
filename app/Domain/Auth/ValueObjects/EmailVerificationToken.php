<?php

declare(strict_types=1);

namespace Domain\Auth\ValueObjects;

use DateTimeImmutable;

final class EmailVerificationToken
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $hashedToken,
        public readonly DateTimeImmutable $expiresAt,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }
}
