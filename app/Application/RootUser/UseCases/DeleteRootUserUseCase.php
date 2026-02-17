<?php

declare(strict_types=1);

namespace Application\RootUser\UseCases;

use Application\RootUser\DTOs\DeleteRootUserRequest;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\Services\LastActiveUserGuard;

class DeleteRootUserUseCase
{
    public function __construct(
        private readonly RootUserRepositoryInterface $userRepository,
        private readonly LastActiveUserGuard $lastActiveGuard
    ) {}

    public function execute(DeleteRootUserRequest $request): void
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
