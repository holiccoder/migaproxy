<?php

use Database\Seeders\HelpCenterSeeder;

test('help center categories endpoint returns sample data', function () {
    $this->seed(HelpCenterSeeder::class);

    $response = $this->getJson('/api/v1/help-center/categories');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.slug', 'billing');
});

test('help center article detail endpoint returns sections and related articles', function () {
    $this->seed(HelpCenterSeeder::class);

    $response = $this->getJson('/api/v1/help-center/articles/setup-api-access');

    $response
        ->assertOk()
        ->assertJsonPath('data.slug', 'setup-api-access')
        ->assertJsonStructure([
            'data' => [
                'slug',
                'title',
                'description',
                'sections' => [
                    '*' => ['id', 'title', 'paragraphs', 'callout', 'codeBlock'],
                ],
                'related_articles',
            ],
        ]);
});
