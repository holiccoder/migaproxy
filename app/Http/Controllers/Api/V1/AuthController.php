<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->create($validated);
        event(new Registered($user));

        return response()->json([
            'message' => 'Registration successful. Please verify your email before logging in.',
            'data' => [
                'user' => array_merge($user->toArray(), [
                    'email_verified' => $user->hasVerifiedEmail(),
                ]),
                'email_verification_required' => true,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Please verify your email before logging in.',
                'email_verification_required' => true,
            ], 403);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        $token = $user->createToken('api-token')->plainTextToken;
        $ipmartAccount = $user->ipmart()->first(['ipmart_id', 'plan_balance', 'proxyName', 'proxyPwd']);
        $userPayload = array_merge($user->toArray(), [
            'email_verified' => $user->hasVerifiedEmail(),
            'ipmart' => [
                'ipmart_id' => $ipmartAccount?->ipmart_id,
                'proxyName' => $ipmartAccount?->proxyName,
                'proxyPwd' => $ipmartAccount?->proxyPwd,
                'plan_balance' => $ipmartAccount?->plan_balance,
            ],
        ]);

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => $userPayload,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}
