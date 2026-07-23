<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{


    public function adminLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'data' => $user->load('schoolLevel'),
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $user->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');

        if (! $token = $guard->attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        /** @var User $user */
        $user = $guard->user();

        return response()->json([
            'user' => $user->load('schoolLevel'),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ]);
    }

    // Updated register to return token
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'school_level_id' => ['required', 'integer', Rule::exists('school_levels', 'id')],
        ]);

        // Prevent creation of admin accounts via public registration
        $adminEmail = 'admin@mathtrainer.app';
        if (strtolower($validated['email']) === strtolower($adminEmail)) {
            return response()->json(['error' => 'Registration of admin account is not allowed.'], 403);
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'school_level_id' => $validated['school_level_id'],
            'role' => 'student', // force student role
        ]);

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');

        // Generate JWT token for the newly created user
        $token = $guard->login($user);

        return response()->json([
            'data' => $user->load('schoolLevel'),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ], 201);
    }
}
