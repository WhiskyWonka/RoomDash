<?php

declare(strict_types=1);

namespace Application\AuditLog\DTOs;

final class RecordAuditLogRequest
{
    public function __construct(
        public readonly string $actorId,
        public readonly string $action,
        public readonly ?string $entityType = null,
        public readonly ?string $entityId = null,
        public readonly ?array $oldValues = null,
        public readonly ?array $newValues = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
