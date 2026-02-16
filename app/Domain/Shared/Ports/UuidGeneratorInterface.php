<?php

namespace Domain\Shared\Ports;

interface UuidGeneratorInterface
{
    public function generate(): string;

    public function validate(string $uuid): bool;
}
