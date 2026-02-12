<?php

declare(strict_types=1);

namespace Infrastructure\AuditLog\Models;

use Database\Factories\AuditLogFactory;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog as AuditLogEntity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): AuditLogFactory
    {
        return AuditLogFactory::new();
    }

    public function toEntity(): AuditLogEntity
    {
        return new AuditLogEntity(
            id: $this->id,
            userId: $this->user_id,
            action: $this->action,
            entityType: $this->entity_type,
            entityId: $this->entity_id,
            oldValues: $this->old_values,
            newValues: $this->new_values,
            ipAddress: $this->ip_address,
            userAgent: $this->user_agent,
            createdAt: new DateTimeImmutable($this->created_at->toDateTimeString()),
        );
    }
}
