<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $ipmartAccount = $user->ipmart()->first(['available_traffic', 'plan_balance', 'proxyName', 'proxyPwd']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'email_verified' => $user->hasVerifiedEmail(),
            'avatar_path' => $user->avatar_path,
            'avatar_url' => $user->avatar_path ? asset('storage/'.$user->avatar_path) : null,
            'skype_profile' => $user->skype_profile,
            'telegram_profile' => $user->telegram_profile,
            'facebook_profile' => $user->facebook_profile,
            'x_profile' => $user->x_profile,
            'youtube_profile' => $user->youtube_profile,
            'instagram_profile' => $user->instagram_profile,
            'balance' => $user->balance,
            'available_traffic' => $ipmartAccount?->available_traffic,
            'plan_balance' => $ipmartAccount?->plan_balance,
            'proxyName' => $ipmartAccount?->proxyName,
            'proxyPwd' => $ipmartAccount?->proxyPwd,
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validated();
        $avatarPath = $user->avatar_path;

        if ($request->hasFile('avatar')) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $normalizeSocial = static fn (?string $value): ?string => filled($value) ? trim($value) : null;

        $user->forceFill([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'email' => $validated['email'],
            'avatar_path' => $avatarPath,
            'skype_profile' => $normalizeSocial($validated['skype_profile'] ?? null),
            'telegram_profile' => $normalizeSocial($validated['telegram_profile'] ?? null),
            'facebook_profile' => $normalizeSocial($validated['facebook_profile'] ?? null),
            'x_profile' => $normalizeSocial($validated['x_profile'] ?? null),
            'youtube_profile' => $normalizeSocial($validated['youtube_profile'] ?? null),
            'instagram_profile' => $normalizeSocial($validated['instagram_profile'] ?? null),
        ])->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'email_verified' => $user->hasVerifiedEmail(),
                    'avatar_path' => $user->avatar_path,
                    'avatar_url' => $user->avatar_path ? asset('storage/'.$user->avatar_path) : null,
                    'skype_profile' => $user->skype_profile,
                    'telegram_profile' => $user->telegram_profile,
                    'facebook_profile' => $user->facebook_profile,
                    'x_profile' => $user->x_profile,
                    'youtube_profile' => $user->youtube_profile,
                    'instagram_profile' => $user->instagram_profile,
                ],
            ],
        ]);
    }
}
