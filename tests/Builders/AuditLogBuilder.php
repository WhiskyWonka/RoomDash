<?php

declare(strict_types=1);

namespace Tests\Builders;

use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;

final class AuditLogBuilder
{
    private string $id = 'log-uuid-1234';
    private string $userId = 'actor-uuid-1234';
    private string $action = 'root_user.created';
    private ?string $entityType = 'root_user';
    private ?string $entityId = 'target-uuid-1234';
    private ?array $oldValues = null;
    private ?array $newValues = null;
    private ?string $ipAddress = '127.0.0.1';
    private ?string $userAgent = 'TestAgent/1.0';
    private ?DateTimeImmutable $createdAt = null;

    public function withId(string $id): static
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withUserId(string $userId): static
    {
        $clone = clone $this;
        $clone->userId = $userId;
        return $clone;
    }

    public function withAction(string $action): static
    {
        $clone = clone $this;
        $clone->action = $action;
        return $clone;
    }

    public function withEntityType(?string $entityType): static
    {
        $clone = clone $this;
        $clone->entityType = $entityType;
        return $clone;
    }

    public function withEntityId(?string $entityId): static
    {
        $clone = clone $this;
        $clone->entityId = $entityId;
        return $clone;
    }

    public function withOldValues(?array $oldValues): static
    {
        $clone = clone $this;
        $clone->oldValues = $oldValues;
        return $clone;
    }

    public function withNewValues(?array $newValues): static
    {
        $clone = clone $this;
        $clone->newValues = $newValues;
        return $clone;
    }

    public function build(): AuditLog
    {
        return new AuditLog(
            id: $this->id,
            userId: $this->userId,
            action: $this->action,
            entityType: $this->entityType,
            entityId: $this->entityId,
            oldValues: $this->oldValues,
            newValues: $this->newValues,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
            createdAt: $this->createdAt ?? new DateTimeImmutable(),
        );
    }
}
