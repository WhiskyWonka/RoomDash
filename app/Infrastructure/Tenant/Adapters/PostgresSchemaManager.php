<?php

declare(strict_types=1);

namespace Infrastructure\Tenant\Adapters;

use Domain\Tenant\Ports\SchemaManagerInterface;
use Illuminate\Support\Facades\DB;

class PostgresSchemaManager implements SchemaManagerInterface
{
    public function createSchema(string $schemaName): void
    {
        DB::statement("CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\"");
    }

    public function dropSchema(string $schemaName): void
    {
        DB::statement("DROP SCHEMA IF EXISTS \"{$schemaName}\" CASCADE");
    }

    public function schemaExists(string $schemaName): bool
    {
        return DB::selectOne(
            "SELECT EXISTS (SELECT 1 FROM information_schema.schemata WHERE schema_name = ?)",
            [$schemaName]
        )->exists;
    }
}
