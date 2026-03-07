<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->create($validated);

        UserRegistered::dispatch($user, $validated['password']);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
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

        $token = $user->createToken('api-token')->plainTextToken;
        $ipmartAccount = $user->ipmart()->first(['plan_balance', 'proxyName', 'proxyPwd']);
        $userPayload = array_merge($user->toArray(), [
            'plan_balance' => $ipmartAccount?->plan_balance,
            'proxyName' => $ipmartAccount?->proxyName,
            'proxyPwd' => $ipmartAccount?->proxyPwd,
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
