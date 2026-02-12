<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome 10% Off',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => 10,
                'is_active' => true,
                'usage_limit' => null,
                'used_count' => 0,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
            ],
            [
                'code' => 'SAVE25',
                'name' => 'Save 25% Off',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => 25,
                'is_active' => true,
                'usage_limit' => 200,
                'used_count' => 0,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'code' => 'TAKE500',
                'name' => 'Take $5.00 Off',
                'type' => Coupon::TYPE_FIXED,
                'value' => 500,
                'is_active' => true,
                'usage_limit' => 500,
                'used_count' => 0,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
            ],
            [
                'code' => 'TAKE2000',
                'name' => 'Take $20.00 Off',
                'type' => Coupon::TYPE_FIXED,
                'value' => 2000,
                'is_active' => true,
                'usage_limit' => 100,
                'used_count' => 0,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(2),
            ],
        ];

        foreach ($coupons as $couponData) {
            Coupon::query()->updateOrCreate(
                ['code' => $couponData['code']],
                $couponData,
            );
        }
    }
}
