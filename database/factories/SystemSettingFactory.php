<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2, '_'),
            'value' => fake()->word(),
        ];
    }

    public function frontendLogoPath(?string $path = 'branding/logo.svg'): static
    {
        return $this->state(fn (): array => [
            'key' => SystemSetting::KEY_FRONTEND_LOGO_PATH,
            'value' => $path,
        ]);
    }

    public function frontendFaviconPath(?string $path = 'branding/favicon.ico'): static
    {
        return $this->state(fn (): array => [
            'key' => SystemSetting::KEY_FRONTEND_FAVICON_PATH,
            'value' => $path,
        ]);
    }

    public function defaultAffiliateCommissionRate(int $rate = SystemSetting::DEFAULT_AFFILIATE_COMMISSION_RATE): static
    {
        return $this->state(fn (): array => [
            'key' => SystemSetting::KEY_DEFAULT_AFFILIATE_COMMISSION_RATE,
            'value' => (string) $rate,
        ]);
    }
}
