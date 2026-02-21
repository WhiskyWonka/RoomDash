<?php

declare(strict_types=1);

namespace Application\EmailVerification\UseCases;

use Application\EmailVerification\DTOs\VerifyEmailRequest;
use Domain\Auth\Exceptions\ExpiredTokenException;
use Domain\Auth\Exceptions\InvalidTokenException;
use Domain\Auth\Ports\EmailVerificationTokenRepositoryInterface;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Shared\Ports\PasswordHasherInterface;

class VerifyEmailUseCase
{
    public function __construct(
        private readonly EmailVerificationTokenRepositoryInterface $tokenRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @throws InvalidTokenException
     * @throws ExpiredTokenException
     */
    public function execute(VerifyEmailRequest $request): void
    {
        $hashedToken = hash('sha256', $request->token);

        $token = $this->tokenRepository->findByHashedToken($hashedToken);

        if ($token === null) {
            throw new InvalidTokenException('Invalid verification token');
        }

        if ($token->isExpired()) {
            throw new ExpiredTokenException('Verification token has expired');
        }

        // Set email as verified and set password
        $hashedPassword = $this->passwordHasher->hash($request->password);
        $this->userRepository->verifyEmail($token->userId, $hashedPassword);

        // Consume token
        $this->tokenRepository->deleteByUserId($token->userId);
    }
}
