<?php

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

test('branding settings endpoint returns null logo when setting is missing', function () {
    $response = $this->getJson('/api/v1/settings/branding');

    $response
        ->assertOk()
        ->assertJsonPath('data.logo_path', null)
        ->assertJsonPath('data.logo_url', null);
});

test('branding settings endpoint returns logo url when configured logo exists', function () {
    Storage::fake('public');
    Storage::disk('public')->put('branding/frontend-logo.svg', '<svg></svg>');

    SystemSetting::factory()
        ->frontendLogoPath('branding/frontend-logo.svg')
        ->create();

    $response = $this->getJson('/api/v1/settings/branding');

    $response
        ->assertOk()
        ->assertJsonPath('data.logo_path', 'branding/frontend-logo.svg');

    expect((string) $response->json('data.logo_url'))
        ->toContain('/storage/branding/frontend-logo.svg');
});
