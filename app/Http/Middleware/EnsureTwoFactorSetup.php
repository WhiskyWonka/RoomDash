<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Auth\Ports\AdminUserRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorSetup
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $users,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('admin_user_id');
        if (! $userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = $this->users->findById($userId);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (! $user->twoFactorEnabled) {
            return response()->json([
                'message' => '2FA setup required',
                'code' => '2FA_SETUP_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
