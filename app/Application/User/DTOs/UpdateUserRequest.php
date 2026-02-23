<?php

declare(strict_types=1);

namespace Application\User\DTOs;

final class UpdateUserRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $actorId,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
