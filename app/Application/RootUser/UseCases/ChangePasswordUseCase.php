<?php

declare(strict_types=1);

namespace Application\RootUser\UseCases;

use Application\RootUser\DTOs\ChangePasswordRequest;
use Domain\Auth\Exceptions\InvalidCurrentPasswordException;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Shared\Ports\PasswordHasherInterface;

class ChangePasswordUseCase
{
    public function __construct(
        private readonly RootUserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @throws InvalidCurrentPasswordException
     */
    public function execute(ChangePasswordRequest $request): void
    {
        $user = $this->userRepository->findById($request->id);

        // Self-change: verify current password
        if ($request->actorId === $request->id) {
            if (! $this->passwordHasher->check($request->currentPassword ?? '', $user->password)) {
                throw new InvalidCurrentPasswordException('Current password is incorrect');
            }
        }

        $this->userRepository->update($request->id, [
            'password' => $this->passwordHasher->hash($request->newPassword),
        ]);
    }
}
