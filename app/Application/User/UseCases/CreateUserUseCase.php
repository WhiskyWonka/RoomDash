<?php

declare(strict_types=1);

namespace Application\User\UseCases;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use Application\User\DTOs\CreateUserRequest;
// use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Entities\User;
use Domain\Auth\Exceptions\DuplicateEmailException;
use Domain\Auth\Exceptions\DuplicateUsernameException;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Auth\ValueObjects\Username;
use Domain\Shared\Ports\PasswordHasherInterface;
use Domain\Shared\Ports\UuidGeneratorInterface;

class CreateUserUseCase
{
    use ApiResponse;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EmailVerificationServiceInterface $emailService,
        // private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly PasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @throws DuplicateEmailException
     * @throws DuplicateUsernameException
     */
    public function execute(CreateUserRequest $request): User
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
            'password' => $this->passwordHasher->hash($request->password),
        ]);

        // Send verification email
        $this->emailService->sendVerificationEmail($user->id);

        return $user;
    }
}
