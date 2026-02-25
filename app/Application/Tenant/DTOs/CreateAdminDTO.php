<?php

declare(strict_types=1);

namespace Application\Tenant\DTOs;

final class CreateAdminDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $username,
        public readonly string $firstName,
        public readonly string $lastName,
    ) {}
}
