<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::serializeUsing(function ($carbon) {
            return $carbon->format('Y-m-d H:i:s');
        });

        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            $backendVerificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ],
            );

            $queryParameters = [];
            parse_str((string) parse_url($backendVerificationUrl, PHP_URL_QUERY), $queryParameters);

            $frontendBaseUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
            $frontendVerificationQuery = http_build_query([
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
                'expires' => $queryParameters['expires'] ?? null,
                'signature' => $queryParameters['signature'] ?? null,
            ], '', '&', PHP_QUERY_RFC3986);

            return "{$frontendBaseUrl}/email-verification?{$frontendVerificationQuery}";
        });
    }
}
