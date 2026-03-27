<?php

use App\Models\CmsPage;

test('cms pages index returns only published pages', function () {
    $published = CmsPage::factory()->create([
        'status' => CmsPage::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
    ]);
    $draft = CmsPage::factory()->create([
        'status' => CmsPage::STATUS_DRAFT,
        'published_at' => null,
    ]);

    $response = $this->getJson('/api/v1/cms-pages');

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)
        ->toContain($published->slug)
        ->not->toContain($draft->slug);
});

test('cms pages index can be filtered by search', function () {
    $matching = CmsPage::factory()->create([
        'title' => 'Pricing Page',
        'status' => CmsPage::STATUS_PUBLISHED,
        'published_at' => now()->subDays(2),
    ]);
    $nonMatching = CmsPage::factory()->create([
        'title' => 'Company Story',
        'status' => CmsPage::STATUS_PUBLISHED,
        'published_at' => now()->subDays(3),
    ]);

    $response = $this->getJson('/api/v1/cms-pages?search=Pricing');

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)
        ->toContain($matching->slug)
        ->not->toContain($nonMatching->slug);
});

test('cms pages show returns a published page by slug', function () {
    $page = CmsPage::factory()->create([
        'content' => "# CMS Heading\n\nCMS paragraph body.",
        'status' => CmsPage::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
    ]);
    $page->seo()->update([
        'title' => 'CMS SEO Title',
        'description' => 'CMS SEO Description',
        'canonical_url' => 'https://example.test/pages/'.$page->slug,
    ]);

    $response = $this->getJson("/api/v1/cms-pages/{$page->slug}");

    $response
        ->assertOk()
        ->assertJsonPath('data.slug', $page->slug)
        ->assertJsonPath('data.seo.title', 'CMS SEO Title')
        ->assertJsonPath('data.seo.description', 'CMS SEO Description')
        ->assertJsonPath('data.seo.canonical_url', 'https://example.test/pages/'.$page->slug)
        ->assertJsonPath('data.seo.open_graph.title', 'CMS SEO Title')
        ->assertJsonPath('data.seo.open_graph.type', 'website')
        ->assertJsonPath('data.seo.twitter_card.title', 'CMS SEO Title')
        ->assertJsonPath('data.seo.twitter_card.card', 'summary');

    expect($response->json('data.content_html'))
        ->toContain('<h1>CMS Heading</h1>')
        ->toContain('<p>CMS paragraph body.</p>');
});

test('cms pages show rejects unpublished pages', function () {
    $draft = CmsPage::factory()->create([
        'status' => CmsPage::STATUS_DRAFT,
        'published_at' => null,
    ]);
    $scheduled = CmsPage::factory()->create([
        'status' => CmsPage::STATUS_PUBLISHED,
        'published_at' => now()->addDay(),
    ]);

    $this->getJson("/api/v1/cms-pages/{$draft->slug}")->assertNotFound();
    $this->getJson("/api/v1/cms-pages/{$scheduled->slug}")->assertNotFound();
});
