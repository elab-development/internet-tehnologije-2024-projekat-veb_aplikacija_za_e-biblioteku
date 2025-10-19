<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required|string|in:basic,premium',
            'duration' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        // Check if user already has active subscription
        $existingSubscription = Subscription::where('user_id', $user->id)
            ->where('ends_at', '>', now())
            ->first();

        if ($existingSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'User already has an active subscription',
            ], 400);
        }

        // Create new subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan' => $request->plan,
            'starts_at' => now(),
            'ends_at' => now()->addDays($request->duration),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully',
            'data' => [
                'subscription' => $subscription,
                'active' => $subscription->active,
            ],
        ], 201);
    }

    public function status(): JsonResponse
    {
        $user = Auth::user();

        $subscription = Subscription::where('user_id', $user->id)
            ->where('ends_at', '>', now())
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'data' => [
                    'active' => false,
                    'plan' => null,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'active' => $subscription->isActive(),
                'plan' => $subscription->plan,
                'starts_at' => $subscription->starts_at,
                'ends_at' => $subscription->ends_at,
            ],
        ]);
    }

    public function history(): JsonResponse
    {
        $user = Auth::user();

        $subscriptions = Subscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
        ]);
    }
}
