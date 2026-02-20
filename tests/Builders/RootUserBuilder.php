<?php

declare(strict_types=1);

namespace Tests\Builders;

use DateTimeImmutable;
use Domain\Auth\Entities\RootUser;
use Domain\Auth\ValueObjects\Username;

final class RootUserBuilder
{
    private string $id = 'user-uuid-1234';
    private string $username = 'jdoe';
    private string $firstName = 'John';
    private string $lastName = 'Doe';
    private string $email = 'john@example.com';
    private bool $isActive = true;
    private bool $twoFactorEnabled = false;
    private ?DateTimeImmutable $emailVerifiedAt = null;
    private ?DateTimeImmutable $twoFactorConfirmedAt = null;
    private ?DateTimeImmutable $createdAt = null;

    public function withId(string $id): static
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withUsername(string $username): static
    {
        $clone = clone $this;
        $clone->username = $username;
        return $clone;
    }

    public function withEmail(string $email): static
    {
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function withFirstName(string $firstName): static
    {
        $clone = clone $this;
        $clone->firstName = $firstName;
        return $clone;
    }

    public function withLastName(string $lastName): static
    {
        $clone = clone $this;
        $clone->lastName = $lastName;
        return $clone;
    }

    public function inactive(): static
    {
        $clone = clone $this;
        $clone->isActive = false;
        return $clone;
    }

    public function verified(): static
    {
        $clone = clone $this;
        $clone->emailVerifiedAt = new DateTimeImmutable();
        return $clone;
    }

    public function withTwoFactor(): static
    {
        $clone = clone $this;
        $clone->twoFactorEnabled = true;
        $clone->twoFactorConfirmedAt = new DateTimeImmutable();
        return $clone;
    }

    public function build(): RootUser
    {
        return new RootUser(
            id: $this->id,
            username: new Username($this->username),
            firstName: $this->firstName,
            lastName: $this->lastName,
            email: $this->email,
            isActive: $this->isActive,
            twoFactorEnabled: $this->twoFactorEnabled,
            emailVerifiedAt: $this->emailVerifiedAt,
            twoFactorConfirmedAt: $this->twoFactorConfirmedAt,
            createdAt: $this->createdAt ?? new DateTimeImmutable(),
        );
    }
}
