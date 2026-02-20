<?php

declare(strict_types=1);

namespace Application\RootUser\UseCases;

use Application\RootUser\DTOs\UpdateRootUserRequest;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Entities\RootUser;
use Domain\Auth\Exceptions\DuplicateEmailException;
use Domain\Auth\Exceptions\DuplicateUsernameException;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\ValueObjects\Username;
use Domain\Shared\Ports\UuidGeneratorInterface;

class UpdateRootUserUseCase
{
    public function __construct(
        private readonly RootUserRepositoryInterface $userRepository,
        private readonly EmailVerificationServiceInterface $emailService,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly UuidGeneratorInterface $uuidGenerator
    ) {}

    public function execute(UpdateRootUserRequest $request): RootUser
    {
        $existingUser = $this->userRepository->findById($request->id);

        // Validate username format
        new Username($request->username);

        // Check email uniqueness (if changed)
        if ($request->email !== $existingUser->email) {
            if ($this->userRepository->existsByEmail($request->email)) {
                throw new DuplicateEmailException('Email already exists');
            }
        }

        // Check username uniqueness (if changed)
        if ($request->username !== $existingUser->username->value()) {
            if ($this->userRepository->existsByUsername($request->username)) {
                throw new DuplicateUsernameException('Username already exists');
            }
        }

        // Update user
        $updatedUser = $this->userRepository->update($request->id, [
            'username' => $request->username,
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'email' => $request->email,
        ]);

        // Handle email change re-verification (BR-011)
        $emailChanged = $request->email !== $existingUser->email;
        if ($emailChanged) {
            $this->userRepository->clearEmailVerification($request->id);
            $this->emailService->invalidatePreviousTokens($request->id);
            $this->emailService->sendVerificationEmail($request->id);
        }

        return $updatedUser;
    }
}
