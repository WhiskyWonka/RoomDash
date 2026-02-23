<?php

declare(strict_types=1);

namespace Application\User\UseCases;

use Application\User\DTOs\DeleteUserRequest;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Auth\Services\LastActiveUserGuard;

class DeleteUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly LastActiveUserGuard $lastActiveGuard
    ) {}

    public function execute(DeleteUserRequest $request): void
    {
        $user = $this->userRepository->findById($request->id);

        // Check self-deletion
        $user->assertCannotDeleteSelf($request->actorId);

        // Check last active user protection
        $this->lastActiveGuard->assertCanDelete($request->id);

        // Delete user
        $this->userRepository->delete($request->id);
    }
}
