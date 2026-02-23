<?php

declare(strict_types=1);

namespace Infrastructure\Tenant\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasFactory, SoftDeletes;

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'is_active',
            'plan',
            'deleted_at',
        ];
    }
}
