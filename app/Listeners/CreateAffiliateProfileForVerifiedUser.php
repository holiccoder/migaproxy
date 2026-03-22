<?php

namespace App\Listeners;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CreateAffiliateProfileForVerifiedUser
{
    private const DEFAULT_COMMISSION_VALUE = 10;

    private const DEFAULT_COOKIE_DAYS = 30;

    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;

        try {
            $existingAffiliate = Affiliate::query()
                ->where('user_id', $user->id)
                ->first();

            if ($existingAffiliate instanceof Affiliate) {
                return;
            }

            Affiliate::query()->create([
                'user_id' => $user->id,
                'name' => $user->name.' Affiliate',
                'code' => $this->generateUniqueAffiliateCode($user->id),
                'commission_type' => Affiliate::COMMISSION_TYPE_PERCENTAGE,
                'commission_value' => self::DEFAULT_COMMISSION_VALUE,
                'cookie_days' => self::DEFAULT_COOKIE_DAYS,
                'is_active' => true,
                'total_earnings' => 0,
            ]);
        } catch (Throwable $throwable) {
            Log::error('Affiliate profile creation listener failed.', [
                'user_id' => $user->id,
                'message' => $throwable->getMessage(),
            ]);
        }
    }

    private function generateUniqueAffiliateCode(int|string $userId): string
    {
        $preferredCode = 'AFFU'.(string) $userId;

        if (Affiliate::query()->where('code', $preferredCode)->doesntExist()) {
            return $preferredCode;
        }

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $randomCode = 'AFF'.Str::upper(Str::random(8));

            if (Affiliate::query()->where('code', $randomCode)->doesntExist()) {
                return $randomCode;
            }
        }

        return 'AFF'.Str::upper((string) Str::uuid());
    }
}
