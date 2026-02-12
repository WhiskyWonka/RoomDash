<?php

declare(strict_types=1);

namespace Application\RootUser\DTOs;

final class DeleteRootUserRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $actorId,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
