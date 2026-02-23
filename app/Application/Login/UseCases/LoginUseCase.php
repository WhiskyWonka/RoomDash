<?php

declare(strict_types=1);

namespace Application\Login\UseCases;

use Application\Login\DTOs\UserLoginDTO;
use Domain\Auth\Entities\User;
use Domain\Auth\Exceptions\AccountDeactivatedException;
use Domain\Auth\Exceptions\EmailNotVerifiedException;
use Domain\Auth\Exceptions\InvalidCredentialsException;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Shared\Ports\PasswordHasherInterface;

class LoginUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $hasherService,
    ) {}

    public function execute(UserLoginDTO $request): User
    {
        // Check if user exists with this email before verifying password
        // We need the Eloquent model to check is_active and email_verified_at
        $user = $this->userRepository->findByEmail($request->email);

        if (! $user || ! $user->password || ! $this->hasherService->check($request->password, $user->password)) {
            throw new InvalidCredentialsException('Invalid credentials', 401);
        }

        // Check if email is verified (BR-009)
        if ($user->emailVerifiedAt === null) {
            throw new EmailNotVerifiedException('Email not verified', 401);
        }

        // Check if account is active (BR-008)
        if (! $user->isActive) {
            throw new AccountDeactivatedException('Account deactivated', 401);
        }

        return $user;

    }
}
