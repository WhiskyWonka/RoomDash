<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Require2FA
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('admin_user_id')) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (! $request->session()->get('2fa_verified', false)) {
            return response()->json([
                'message' => '2FA verification required',
                'code' => '2FA_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
