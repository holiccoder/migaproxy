<?php

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

test('branding settings endpoint returns null branding assets when setting is missing', function () {
    $response = $this->getJson('/api/v1/settings/branding');

    $response
        ->assertOk()
        ->assertJsonPath('data.logo_path', null)
        ->assertJsonPath('data.logo_url', null)
        ->assertJsonPath('data.favicon_path', null)
        ->assertJsonPath('data.favicon_url', null);
});

test('branding settings endpoint returns configured logo and favicon urls', function () {
    Storage::fake('public');
    Storage::disk('public')->put('branding/frontend-logo.svg', '<svg></svg>');
    Storage::disk('public')->put('branding/frontend-favicon.ico', 'ico-data');

    SystemSetting::factory()
        ->frontendLogoPath('branding/frontend-logo.svg')
        ->create();

    SystemSetting::factory()
        ->frontendFaviconPath('branding/frontend-favicon.ico')
        ->create();

    $response = $this->getJson('/api/v1/settings/branding');

    $response
        ->assertOk()
        ->assertJsonPath('data.logo_path', 'branding/frontend-logo.svg')
        ->assertJsonPath('data.favicon_path', 'branding/frontend-favicon.ico');

    expect((string) $response->json('data.logo_url'))
        ->toContain('/storage/branding/frontend-logo.svg');

    expect((string) $response->json('data.favicon_url'))
        ->toContain('/storage/branding/frontend-favicon.ico');
});
