<?php

declare(strict_types=1);

namespace Application\RootUser\UseCases;

use Application\RootUser\DTOs\CreateRootUserRequest;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Entities\RootUser;
use Domain\Auth\Exceptions\DuplicateEmailException;
use Domain\Auth\Exceptions\DuplicateUsernameException;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\ValueObjects\Username;
use Illuminate\Support\Str;

class CreateRootUserUseCase
{
    public function __construct(
        private readonly RootUserRepositoryInterface $userRepository,
        private readonly EmailVerificationServiceInterface $emailService,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @throws DuplicateEmailException
     * @throws DuplicateUsernameException
     */
    public function execute(CreateRootUserRequest $request): RootUser
    {
        // Validate uniqueness
        if ($this->userRepository->existsByEmail($request->email)) {
            throw new DuplicateEmailException('Email already exists');
        }

        if ($this->userRepository->existsByUsername($request->username)) {
            throw new DuplicateUsernameException('Username already exists');
        }

        // Validate username format (will throw InvalidUsernameException if invalid)
        new Username($request->username);

        // Create user
        $user = $this->userRepository->create([
            'username' => $request->username,
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'email' => $request->email,
        ]);

        // Send verification email
        $this->emailService->sendVerificationEmail($user->id);

        // Record audit log
        $this->auditLogRepository->create(new AuditLog(
            id: Str::uuid()->toString(),
            userId: $request->actorId,
            action: 'root_user.created',
            entityType: 'root_user',
            entityId: $user->id,
            oldValues: null,
            newValues: [
                'username' => $user->username->value(),
                'first_name' => $user->firstName,
                'last_name' => $user->lastName,
                'email' => $user->email,
            ],
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
            createdAt: new DateTimeImmutable(),
        ));

        return $user;
    }
}
