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
    /**
     * @var array<string, array{id_column: string, label: string}>
     */
    private const PROVIDERS = [
        'github' => [
            'id_column' => 'github_id',
            'label' => 'GitHub',
        ],
        'google' => [
            'id_column' => 'google_id',
            'label' => 'Google',
        ],
        'x' => [
            'id_column' => 'x_id',
            'label' => 'X',
        ],
    ];

    public function redirectToGithub(): RedirectResponse
    {
        return $this->redirectToProvider('github');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return $this->redirectToProvider('google');
    }

    public function redirectToX(): RedirectResponse
    {
        return $this->redirectToProvider('x');
    }

    public function handleGithubCallback(): JsonResponse
    {
        return $this->handleProviderCallback('github');
    }

    public function handleGoogleCallback(): JsonResponse
    {
        return $this->handleProviderCallback('google');
    }

    public function handleXCallback(): JsonResponse
    {
        return $this->handleProviderCallback('x');
    }

    private function redirectToProvider(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)
            ->stateless()
            ->redirect();
    }

    private function handleProviderCallback(string $provider): JsonResponse
    {
        $providerConfig = self::PROVIDERS[$provider] ?? null;

        if (! is_array($providerConfig)) {
            return response()->json([
                'message' => 'Unsupported social provider.',
            ], 422);
        }

        try {
            $socialiteUser = Socialite::driver($provider)
                ->stateless()
                ->user();
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => "{$providerConfig['label']} authentication failed.",
            ], 422);
        }

        $providerId = $socialiteUser->getId();

        if (! is_string($providerId) || $providerId === '') {
            return response()->json([
                'message' => "{$providerConfig['label']} account id is missing.",
            ], 422);
        }

        $email = $socialiteUser->getEmail();
        $name = $socialiteUser->getName() ?: $socialiteUser->getNickname() ?: "{$providerConfig['label']} User";
        $providerIdColumn = $providerConfig['id_column'];

        $user = User::query()
            ->where($providerIdColumn, $providerId)
            ->first();

        if (! $user && is_string($email) && $email !== '') {
            $user = User::query()
                ->where('email', $email)
                ->first();
        }

        if (! $user) {
            $user = User::query()->create([
                'name' => $name,
                'email' => is_string($email) && $email !== '' ? $email : "{$provider}-{$providerId}@users.local",
                $providerIdColumn => $providerId,
                'password' => Str::random(40),
                'email_verified_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'name' => $user->name ?: $name,
                $providerIdColumn => $providerId,
            ])->save();
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

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
