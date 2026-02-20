<?php

namespace Infrastructure\Shared\Adapters;

use Domain\Shared\Ports\UuidGeneratorInterface;
use Illuminate\Support\Str;

class LaravelUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Str::uuid()->toString();
    }

    public function validate(string $uuid): bool
    {
        return Str::isUuid($uuid);
    }
}
