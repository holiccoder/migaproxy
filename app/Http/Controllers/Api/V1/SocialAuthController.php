<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirectToGithub(): RedirectResponse
    {
        return Socialite::driver('github')
            ->stateless()
            ->redirect();
    }

    public function handleGithubCallback(): JsonResponse
    {
        try {
            $socialiteUser = Socialite::driver('github')
                ->stateless()
                ->user();
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => 'GitHub authentication failed.',
            ], 422);
        }

        $githubId = $socialiteUser->getId();

        if (! is_string($githubId) || $githubId === '') {
            return response()->json([
                'message' => 'GitHub account id is missing.',
            ], 422);
        }

        $email = $socialiteUser->getEmail();
        $name = $socialiteUser->getName() ?: $socialiteUser->getNickname() ?: 'GitHub User';

        $user = User::query()
            ->where('github_id', $githubId)
            ->first();

        if (! $user && is_string($email) && $email !== '') {
            $user = User::query()
                ->where('email', $email)
                ->first();
        }

        if (! $user) {
            $user = User::query()->create([
                'name' => $name,
                'email' => is_string($email) && $email !== '' ? $email : "github-{$githubId}@users.local",
                'github_id' => $githubId,
                'password' => Str::random(40),
                'email_verified_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'name' => $user->name ?: $name,
                'github_id' => $githubId,
            ])->save();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}
