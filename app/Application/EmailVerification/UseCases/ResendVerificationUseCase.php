<?php

declare(strict_types=1);

namespace Application\EmailVerification\UseCases;

use Application\EmailVerification\DTOs\ResendVerificationRequest;
use Domain\Auth\Exceptions\AlreadyVerifiedException;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\Auth\Ports\RootUserRepositoryInterface;

class ResendVerificationUseCase
{
    public function __construct(
        private readonly RootUserRepositoryInterface $userRepository,
        private readonly EmailVerificationServiceInterface $emailService,
    ) {}

    /**
     * @throws AlreadyVerifiedException
     */
    public function execute(ResendVerificationRequest $request): void
    {
        $user = $this->userRepository->findById($request->userId);

        if ($user->isEmailVerified()) {
            throw new AlreadyVerifiedException('User has already been verified');
        }

        $this->emailService->invalidatePreviousTokens($request->userId);
        $this->emailService->sendVerificationEmail($request->userId);
    }
}
