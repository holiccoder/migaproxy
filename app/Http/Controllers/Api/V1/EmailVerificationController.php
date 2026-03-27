<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ResendEmailVerificationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function verify(int $id, string $hash): JsonResponse
    {
        $user = User::query()->find($id);

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Invalid verification link.',
            ], 404);
        }

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.',
            ], 403);
        }

        $message = 'Email is already verified.';

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            $message = 'Email verified successfully.';
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
            'message' => $message,
            'data' => [
                'user' => $userPayload,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function send(ResendEmailVerificationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if ($user instanceof User && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => 'If the account exists and is not verified, a verification email has been sent.',
        ]);
    }
}
