<?php

declare(strict_types=1);

namespace Application\Tenant\DTOs;

final class CreateAdminRequest
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $username,
        public readonly string $firstName,
        public readonly string $lastName,
    ) {}
}
