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
}
