<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Require2FA
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('admin_user_id')) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $user = Auth::guard('admin')->user();

        $verified = $request->session()->get('2fa_verified', false);
        if ($user && ! $verified) {
            return $this->error('2FA verification required', 403, ['code' => '2FA_REQUIRED']);
        }

        return $next($request);
    }
}
