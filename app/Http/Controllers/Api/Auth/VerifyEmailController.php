<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use Application\EmailVerification\DTOs\VerifyEmailDTO;
use Application\EmailVerification\UseCases\VerifyEmailUseCase;
use Domain\Auth\Exceptions\ExpiredTokenException;
use Domain\Auth\Exceptions\InvalidTokenException;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    public function __construct(
        private readonly VerifyEmailUseCase $verifyEmailUseCase,
    ) {}

    public function __invoke(VerifyEmailRequest $request): JsonResponse
    {

        $data = $request->validated();

        try {
            $this->verifyEmailUseCase->execute(new VerifyEmailDTO(
                token: $data['token'],
                password: $data['password'],
                passwordConfirmation: $data['password_confirmation'],
            ));

            return response()->json(['message' => 'Email verified successfully']);
        } catch (ExpiredTokenException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (InvalidTokenException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
