<?php

namespace Domain\Shared\Ports;

interface PasswordHasherInterface
{
    public function hash(string $password): string;
    public function check(string $password, string $hashedPassword): bool;
}