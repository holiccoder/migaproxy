<?php

namespace App\Listeners;

use App\Models\Ipmart;
use App\Models\User;
use App\Services\Api\IPmart\DataRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CreateIpmartAccountForVerifiedUser
{
    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;

        try {
            $ipmartEmail = $this->generateUniqueIpmartEmail();
            $ipmartPassword = Str::random(16);

            $ipmartAccount = DataRequest::registerUser($ipmartEmail, $ipmartPassword, $user->name);

            if (! is_array($ipmartAccount)) {
                Log::warning('Unable to create IPmart account during email verification.', [
                    'user_id' => $user->id,
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
                    'user_id' => $user->id,
                    'response' => $ipmartAccount,
                ]);

                return;
            }

            Ipmart::query()->updateOrCreate(
                ['user_id' => (string) $user->id],
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
                'user_id' => $user->id,
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
