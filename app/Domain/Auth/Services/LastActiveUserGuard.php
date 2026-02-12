<?php

declare(strict_types=1);

namespace Domain\Auth\Services;

use Domain\Auth\Exceptions\LastActiveUserException;
use Domain\Auth\Ports\RootUserRepositoryInterface;

class LastActiveUserGuard
{
    public function __construct(
        private readonly RootUserRepositoryInterface $repository,
    ) {}

    /**
     * @throws LastActiveUserException if only one active user remains
     */
    public function assertCanDelete(string $userId): void
    {
        if ($this->repository->countActive() <= 1) {
            throw new LastActiveUserException('Cannot delete the last active root user');
        }
    }

    /**
     * @throws LastActiveUserException if only one active user remains
     */
    public function assertCanDeactivate(string $userId): void
    {
        if ($this->repository->countActive() <= 1) {
            throw new LastActiveUserException('Cannot deactivate the last active root user');
        }
    }
}
