<?php

namespace Infrastructure\Shared\Adapters;

use Domain\Shared\Ports\PasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $password): string
    {
        return Hash::make($password);
    }

    public function check(string $password, string $hashedPassword): bool
    {
        return Hash::check($password, $hashedPassword);
    }
}