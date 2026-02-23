<?php

declare(strict_types=1);

namespace Domain\Tenant\Entities;

use DateTimeImmutable;
use JsonSerializable;

final class Tenant implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $domain,
        public readonly string $plan,
        public readonly bool $isActive,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'domain' => $this->domain,
            'plan' => $this->plan,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt->format('c'),
        ];
    }
}
