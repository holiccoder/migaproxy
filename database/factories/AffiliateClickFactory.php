<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\AffiliateClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AffiliateClick>
 */
class AffiliateClickFactory extends Factory
{
    protected $model = AffiliateClick::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'code' => strtoupper(fake()->bothify('AFF###??')),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referrer' => fake()->url(),
            'landing_url' => fake()->url(),
            'clicked_at' => now(),
        ];
    }
}
