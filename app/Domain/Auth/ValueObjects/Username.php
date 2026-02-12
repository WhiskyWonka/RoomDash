<?php

declare(strict_types=1);

namespace Domain\Auth\ValueObjects;

use Domain\Auth\Exceptions\InvalidUsernameException;

final class Username
{
    private string $value;

    public function __construct(string $value)
    {
        if ($value === '') {
            throw new InvalidUsernameException('Username cannot be empty');
        }

        if (str_contains($value, ' ')) {
            throw new InvalidUsernameException('Username cannot contain spaces');
        }

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            throw new InvalidUsernameException('Username contains invalid characters');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
