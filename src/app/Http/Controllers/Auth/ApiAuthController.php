<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscriptions()
            ->where('ends_at', '>', now())
            ->where('starts_at', '<=', now())
            ->first();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ],
                'subscription' => $subscription ? [
                    'plan' => $subscription->plan,
                    'starts_at' => $subscription->starts_at,
                    'ends_at' => $subscription->ends_at,
                    'is_active' => $subscription->isActive(),
                ] : null,
            ],
            'message' => 'User profile retrieved successfully'
        ]);
    }
}
