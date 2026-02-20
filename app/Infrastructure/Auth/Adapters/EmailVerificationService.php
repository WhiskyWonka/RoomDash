<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Adapters;

use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Infrastructure\Auth\Models\EmailVerificationToken;
use Infrastructure\Auth\Models\RootUser;

class EmailVerificationService implements EmailVerificationServiceInterface
{
    public function sendVerificationEmail(string $userId): void
    {
        $user = RootUser::findOrFail($userId);
        $rawToken = Str::random(64);
        $hashedToken = hash('sha256', $rawToken);

        EmailVerificationToken::create([
            'user_id' => $userId,
            'token' => $hashedToken,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
        ]);

        // Send the verification email
        Mail::raw(
            "Verify your email and set your password using this token: {$rawToken}",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify your email - RoomDash');
            }
        );
    }

    public function invalidatePreviousTokens(string $userId): void
    {
        EmailVerificationToken::where('user_id', $userId)->delete();
    }
}
