<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            // Ambil user dari JWT
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Owner akses semua
            if ($user->role === 'owner') {
                return $next($request);
            }

            // Cek apakah role user terdaftar di rute ini
            if (in_array($user->role, $roles)) {
                return $next($request);
            }

            return response()->json([
                'message' => 'Forbidden'
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token invalid'
            ], 401);
        }
    }
}