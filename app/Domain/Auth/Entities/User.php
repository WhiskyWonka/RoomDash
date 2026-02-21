<?php

declare(strict_types=1);

namespace Domain\Auth\Entities;

use DateTimeImmutable;
use Domain\Auth\Exceptions\SelfDeletionException;
use Domain\Auth\ValueObjects\Username;
use JsonSerializable;

final class User implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly Username $username,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $avatarPath,
        public readonly bool $isActive,
        public readonly bool $twoFactorEnabled,
        public readonly ?DateTimeImmutable $emailVerifiedAt,
        public readonly ?DateTimeImmutable $twoFactorConfirmedAt,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    /**
     * Asserts that the actor is not trying to delete themselves.
     *
     * @throws SelfDeletionException if actorId matches this user's id
     */
    public function assertCannotDeleteSelf(string $actorId): void
    {
        if ($this->id === $actorId) {
            throw new SelfDeletionException('Cannot delete your own account');
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username->value(),
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'avatarPath' => $this->avatarPath,
            'isActive' => $this->isActive,
            'twoFactorEnabled' => $this->twoFactorEnabled,
            'emailVerifiedAt' => $this->emailVerifiedAt?->format('c'),
            'twoFactorConfirmedAt' => $this->twoFactorConfirmedAt?->format('c'),
            'createdAt' => $this->createdAt->format('c'),
        ];
    }
}
