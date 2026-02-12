<?php

declare(strict_types=1);

namespace Domain\AuditLog\Entities;

use DateTimeImmutable;
use JsonSerializable;

final class AuditLog implements JsonSerializable
{
    public readonly ?string $userAgent;

    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $action,
        public readonly ?string $entityType,
        public readonly ?string $entityId,
        public readonly ?array $oldValues,
        public readonly ?array $newValues,
        public readonly ?string $ipAddress,
        ?string $userAgent,
        public readonly DateTimeImmutable $createdAt,
    ) {
        // Truncate user agent to 500 characters
        $this->userAgent = $userAgent !== null
            ? mb_substr($userAgent, 0, 500)
            : null;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'action' => $this->action,
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
            'oldValues' => $this->oldValues,
            'newValues' => $this->newValues,
            'ipAddress' => $this->ipAddress,
            'userAgent' => $this->userAgent,
            'createdAt' => $this->createdAt->format('c'),
        ];
    }
}
