<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Application\EmailVerification\DTOs\VerifyEmailRequest;
use Application\EmailVerification\UseCases\VerifyEmailUseCase;
use Domain\Auth\Exceptions\ExpiredTokenException;
use Domain\Auth\Exceptions\InvalidTokenException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __construct(
        private readonly VerifyEmailUseCase $verifyEmailUseCase,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        try {
            $this->verifyEmailUseCase->execute(new VerifyEmailRequest(
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
