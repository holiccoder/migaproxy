<?php

use App\Models\Faq;

test('faq endpoint returns published faqs ordered by sort order', function () {
    $firstFaq = Faq::factory()->create([
        'question' => 'First published question',
        'sort_order' => 1,
        'is_published' => true,
    ]);
    $secondFaq = Faq::factory()->create([
        'question' => 'Second published question',
        'sort_order' => 2,
        'is_published' => true,
    ]);
    Faq::factory()->create([
        'question' => 'Hidden draft question',
        'sort_order' => 0,
        'is_published' => false,
    ]);

    $response = $this->getJson('/api/v1/faq');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $firstFaq->id)
        ->assertJsonPath('data.1.id', $secondFaq->id);
});

test('faq endpoint response only contains public faq fields', function () {
    $faq = Faq::factory()->create([
        'is_published' => true,
    ]);

    $response = $this->getJson('/api/v1/faq');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'question',
                    'answer',
                ],
            ],
        ])
        ->assertJsonMissingPath('data.0.is_published')
        ->assertJsonMissingPath('data.0.sort_order');
});
