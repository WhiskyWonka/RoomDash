<?php

declare(strict_types=1);

namespace Domain\Auth\Entities;

use DateTimeImmutable;
use JsonSerializable;

final class AdminUser implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly bool $twoFactorEnabled,
        public readonly ?DateTimeImmutable $twoFactorConfirmedAt,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'twoFactorEnabled' => $this->twoFactorEnabled,
            'twoFactorConfirmedAt' => $this->twoFactorConfirmedAt?->format('c'),
            'createdAt' => $this->createdAt->format('c'),
        ];
    }
}
