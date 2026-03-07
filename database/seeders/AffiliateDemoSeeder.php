<?php

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateConversion;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AffiliateDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            $users = User::factory()->count(5)->create();
        }

        $plans = Plan::query()
            ->get();

        if ($plans->isEmpty()) {
            $plans = Plan::factory()->count(3)->create();
        }

        $candidateCustomers = $users->values();

        $affiliates = $users->map(function (User $user): Affiliate {
            return Affiliate::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name.' Affiliate',
                    'code' => sprintf('AFFU%04d', $user->id),
                    'commission_type' => Affiliate::COMMISSION_TYPE_PERCENTAGE,
                    'commission_value' => 15,
                    'cookie_days' => 30,
                    'is_active' => true,
                ]
            );
        });

        $referrers = [
            'https://www.google.com',
            'https://www.youtube.com',
            'https://www.linkedin.com',
            'https://www.facebook.com',
            'https://x.com',
            'https://news.ycombinator.com',
            'Direct',
        ];

        $affiliates->each(function (Affiliate $affiliate) use ($referrers, $plans, $candidateCustomers): void {
            if ($affiliate->clicks()->count() < 30) {
                foreach (range(1, 30) as $index) {
                    $referrer = $referrers[array_rand($referrers)];

                    AffiliateClick::query()->create([
                        'affiliate_id' => $affiliate->id,
                        'code' => $affiliate->code,
                        'ip' => fake()->ipv4(),
                        'user_agent' => fake()->userAgent(),
                        'referrer' => $referrer === 'Direct' ? null : $referrer,
                        'landing_url' => fake()->randomElement([
                            'https://sass-starter.test/',
                            'https://sass-starter.test/blog',
                            'https://sass-starter.test/pricing',
                            'https://sass-starter.test/features',
                        ]),
                        'clicked_at' => now()->subDays(31 - $index)->addMinutes(random_int(0, 1440)),
                    ]);
                }
            }

            if ($affiliate->conversions()->count() < 12) {
                foreach (range(1, 12) as $index) {
                    $plan = $plans->random();
                    $referredUser = $candidateCustomers
                        ->where('id', '!=', $affiliate->user_id)
                        ->random();
                    $status = fake()->randomElement([
                        AffiliateConversion::STATUS_PENDING,
                        AffiliateConversion::STATUS_APPROVED,
                        AffiliateConversion::STATUS_PAID,
                        AffiliateConversion::STATUS_REJECTED,
                    ]);
                    $orderTotal = (int) $plan->price;
                    $commissionAmount = $affiliate->calculateCommission($orderTotal);
                    $convertedAt = now()->subDays(40 - $index);

                    $order = Order::query()->create([
                        'public_id' => Str::ulid()->toBase32(),
                        'user_id' => $referredUser->id,
                        'plan_id' => $plan->id,
                        'coupon_id' => null,
                        'affiliate_id' => $affiliate->id,
                        'coupon_code' => null,
                        'affiliate_code' => $affiliate->code,
                        'subscription_id' => null,
                        'provider' => 'fake',
                        'provider_reference' => 'fake_checkout_'.Str::lower(Str::random(12)),
                        'status' => in_array($status, [AffiliateConversion::STATUS_APPROVED, AffiliateConversion::STATUS_PAID], true)
                            ? Order::STATUS_PAID
                            : Order::STATUS_PENDING,
                        'subtotal' => $orderTotal,
                        'discount_total' => 0,
                        'total' => $orderTotal,
                        'currency' => 'USD',
                        'checkout_url' => null,
                        'metadata' => [],
                        'paid_at' => in_array($status, [AffiliateConversion::STATUS_APPROVED, AffiliateConversion::STATUS_PAID], true)
                            ? $convertedAt
                            : null,
                        'failed_at' => null,
                    ]);

                    AffiliateConversion::query()->create([
                        'affiliate_id' => $affiliate->id,
                        'user_id' => $referredUser->id,
                        'order_id' => $order->id,
                        'subscription_id' => null,
                        'amount' => $orderTotal,
                        'commission_amount' => $commissionAmount,
                        'status' => $status,
                        'approved_at' => in_array($status, [AffiliateConversion::STATUS_APPROVED, AffiliateConversion::STATUS_PAID], true)
                            ? $convertedAt
                            : null,
                        'paid_at' => $status === AffiliateConversion::STATUS_PAID
                            ? $convertedAt->copy()->addDays(5)
                            : null,
                    ]);
                }
            }

            $totalEarnings = (int) $affiliate->conversions()
                ->whereIn('status', [AffiliateConversion::STATUS_APPROVED, AffiliateConversion::STATUS_PAID])
                ->sum('commission_amount');

            $affiliate->forceFill([
                'total_earnings' => $totalEarnings,
            ])->save();
        });
    }
}
