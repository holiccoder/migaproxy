<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\Ipmart;
use App\Services\Api\IPmart\DataRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CreateIpmartAccountForRegisteredUser
{
    public function handle(UserRegistered $event): void
    {
        try {
            $ipmartEmail = $this->generateUniqueIpmartEmail();

            $ipmartAccount = DataRequest::registerUser($ipmartEmail, $event->plainPassword, $event->user->name);

            if (! is_array($ipmartAccount)) {
                Log::warning('Unable to create IPmart account during user registration.', [
                    'user_id' => $event->user->id,
                ]);

                return;
            }

            if (
                ! isset($ipmartAccount['ipmart_id']) ||
                ! isset($ipmartAccount['proxyName']) ||
                ! isset($ipmartAccount['proxyPwd']) ||
                ! isset($ipmartAccount['login_name']) ||
                ! isset($ipmartAccount['passwd'])
            ) {
                Log::warning('IPmart registration response is missing required fields.', [
                    'user_id' => $event->user->id,
                    'response' => $ipmartAccount,
                ]);

                return;
            }

            Ipmart::query()->updateOrCreate(
                ['user_id' => (string) $event->user->id],
                [
                    'ipmart_id' => (string) $ipmartAccount['ipmart_id'],
                    'ipmart_email' => (string) ($ipmartAccount['ipmart_email'] ?? $ipmartEmail),
                    'plan_balance' => (string) ($ipmartAccount['plan_balance'] ?? 0),
                    'proxyName' => (string) $ipmartAccount['proxyName'],
                    'proxyPwd' => (string) $ipmartAccount['proxyPwd'],
                    'login_name' => (string) $ipmartAccount['login_name'],
                    'passwd' => (string) $ipmartAccount['passwd'],
                ],
            );
        } catch (Throwable $throwable) {
            Log::error('IPmart account creation listener failed.', [
                'user_id' => $event->user->id,
                'message' => $throwable->getMessage(),
            ]);
        }
    }

    private function generateUniqueIpmartEmail(): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $ipmartEmail = Str::lower(Str::random(12)).'@migaproxy.com';

            if (Ipmart::query()->where('ipmart_email', $ipmartEmail)->doesntExist()) {
                return $ipmartEmail;
            }
        }

        return Str::lower((string) Str::uuid()).'@migaproxy.com';
    }
}
