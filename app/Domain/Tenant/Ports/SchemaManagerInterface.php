<?php

declare(strict_types=1);

namespace Domain\Tenant\Ports;

interface SchemaManagerInterface
{
    public function createSchema(string $schemaName): void;

    public function dropSchema(string $schemaName): void;

    public function schemaExists(string $schemaName): bool;
}
