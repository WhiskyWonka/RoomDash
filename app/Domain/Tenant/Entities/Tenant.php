<?php

declare(strict_types=1);

namespace Domain\Tenant\Entities;

use DateTimeImmutable;

final class Tenant
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $domain,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}
