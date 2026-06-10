<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register
     */
    public function register(Request $request)
    {
        try {

            $validated = $request->validate([

                'name' => 'required|string|max:255',

                'email' => 'required|email|unique:users,email',

                'phone' => 'required|string|unique:users,phone',

                'password' => 'required|string|min:6|confirmed',

                'role' => 'nullable|in:customer,staff,owner',

                'address' => 'nullable|string',

                'city' => 'nullable|string',

                'province' => 'nullable|string',

                'postal_code' => 'nullable|string'

            ]);

            $user = User::create([

                'name' => $validated['name'],

                'email' => $validated['email'],

                'phone' => $validated['phone'],

                'password' => Hash::make(
                    $validated['password']
                ),

                'role' => $validated['role']
                    ?? 'customer',

                'address' => $validated['address']
                    ?? null,

                'city' => $validated['city']
                    ?? null,

                'province' => $validated['province']
                    ?? null,

                'postal_code' => $validated['postal_code']
                    ?? null,

                'is_active' => true

            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([

                'message' =>
                'User berhasil didaftarkan',

                'user' => $user,

                'token' => $token,

                'token_type' => 'Bearer',

                'expires_in' =>
                config('jwt.ttl') * 60

            ], 201);
        } catch (ValidationException $e) {

            return response()->json([

                'message' =>
                'Validasi gagal',

                'errors' =>
                $e->errors()

            ], 422);
        }
    }

    /**
     * Login
     */
    public function login(LoginRequest $request)
    {
        try {

           $credentials = $request->validated();

            if (
                !$token =
                    JWTAuth::attempt($credentials)
            ) {

                return response()->json([

                    'message' =>
                    'Email atau password salah'

                ], 401);
            }

            $user = JWTAuth::user();

            if (!$user) {

                return response()->json([

                    'message' =>
                    'User tidak ditemukan'

                ], 404);
            }

            if (!$user->is_active) {

                JWTAuth::invalidate($token);

                return response()->json([

                    'message' =>
                    'Akun tidak aktif'

                ], 403);
            }

            return response()->json([

                'message' =>
                'Login berhasil',

                'user' =>
                $user,

                'token' =>
                $token,

                'token_type' =>
                'Bearer',

                'expires_in' =>
                config('jwt.ttl') * 60

            ], 200);
        } catch (ValidationException $e) {

            return response()->json([

                'message' =>
                'Validasi gagal',

                'errors' =>
                $e->errors()

            ], 422);
        } catch (\Exception $e) {

            return response()->json([

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }

    /**
     * Current user
     */
    public function me()
    {
        try {

            $user = JWTAuth::parseToken()
                ->authenticate();

            return response()->json([

                'message' =>
                'Data user berhasil diambil',

                'user' =>
                $user

            ]);
        } catch (JWTException $e) {

            return response()->json([

                'message' =>
                'Token tidak valid'

            ], 401);
        }
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        try {

            $newToken = JWTAuth::refresh(
                JWTAuth::getToken()
            );

            return response()->json([

                'message' =>
                'Token berhasil diperbarui',

                'token' =>
                $newToken,

                'token_type' =>
                'Bearer',

                'expires_in' =>
                config('jwt.ttl') * 60

            ]);
        } catch (JWTException $e) {

            return response()->json([

                'message' =>
                'Refresh token gagal'

            ], 401);
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        try {

            $token = JWTAuth::getToken();

            if ($token) {
                JWTAuth::invalidate($token);
            }

            return response()->json([
                'message' => 'Logout berhasil'
            ]);
        } catch (JWTException $e) {

            return response()->json([
                'message' => 'Logout gagal'
            ], 401);
        }
    }
    /**
     * Validate token
     */
    public function validateToken()
    {
        try {

            $user = JWTAuth::parseToken()
                ->authenticate();

            return response()->json([

                'valid' => true,

                'user' => $user

            ]);
        } catch (JWTException $e) {

            return response()->json([

                'valid' => false,

                'message' =>
                'Token tidak valid'

            ], 401);
        }
    }
}
