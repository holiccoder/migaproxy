<?php

namespace Database\Seeders;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Database\Seeder;

class HelpCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'getting-started',
                'icon' => 'rocket',
                'title' => 'Getting Started',
                'description' => 'Everything you need to set up your account.',
            ],
            [
                'slug' => 'integrations',
                'icon' => 'link',
                'title' => 'Integrations',
                'description' => 'Connect with Shopify, Slack, or Zapier.',
            ],
            [
                'slug' => 'security-privacy',
                'icon' => 'shield',
                'title' => 'Security & Privacy',
                'description' => 'GDPR compliance and account safety.',
            ],
            [
                'slug' => 'billing',
                'icon' => 'wallet',
                'title' => 'Billing',
                'description' => 'Invoices, refunds, and subscription details.',
            ],
        ];

        $createdCategories = collect($categories)
            ->mapWithKeys(function (array $category): array {
                $model = HelpCategory::query()->updateOrCreate(
                    ['slug' => $category['slug']],
                    [
                        'icon' => $category['icon'],
                        'title' => $category['title'],
                        'description' => $category['description'],
                    ]
                );

                return [$category['slug'] => $model];
            });

        $articles = [
            [
                'slug' => 'setup-api-access',
                'title' => 'How to Setup API Access',
                'description' => 'Generate your first API token and call your first endpoint securely.',
                'category' => 'getting-started',
                'related_slugs' => ['webhook-quickstart', 'manage-api-keys', 'billing-faq'],
                'sections' => [
                    [
                        'section_key' => 'generate-token',
                        'title' => 'Generate Your API Token',
                        'paragraphs' => [
                            'Open your profile settings and create a personal API token with the minimum scope required.',
                            'Store the token in a secure secret manager and never commit it to source control.',
                        ],
                        'callout_type' => 'warning',
                        'callout_title' => 'Security Warning',
                        'callout_content' => 'API tokens are sensitive credentials. Rotate tokens immediately if exposed.',
                    ],
                    [
                        'section_key' => 'first-request',
                        'title' => 'Make Your First Request',
                        'paragraphs' => [
                            'Use your token as a Bearer token in the Authorization header.',
                            'Start with a read-only endpoint to verify your auth and network setup.',
                        ],
                        'code_language' => 'bash',
                        'code' => "curl -X GET https://sass-starter.test/api/v1/plans \\\n  -H \"Accept: application/json\" \\\n  -H \"Authorization: Bearer YOUR_TOKEN\"",
                    ],
                ],
            ],
            [
                'slug' => 'webhook-quickstart',
                'title' => 'Webhook Quickstart',
                'description' => 'Receive real-time events for payments and lifecycle changes.',
                'category' => 'integrations',
                'related_slugs' => ['setup-api-access', 'manage-api-keys', 'refund-policy'],
                'sections' => [
                    [
                        'section_key' => 'create-endpoint',
                        'title' => 'Create a Public Endpoint',
                        'paragraphs' => [
                            'Your webhook endpoint must be publicly reachable via HTTPS.',
                            'Return a 2xx response quickly, then process asynchronously.',
                        ],
                    ],
                    [
                        'section_key' => 'verify-signature',
                        'title' => 'Verify Event Signatures',
                        'paragraphs' => [
                            'Always verify signatures before trusting webhook payloads.',
                            'Reject unsigned or malformed payloads with a non-2xx response.',
                        ],
                        'code_language' => 'json',
                        'code' => "{\n  \"event\": \"checkout.completed\",\n  \"order_public_id\": \"01JABC...\",\n  \"provider_reference\": \"fake_checkout_abc123\"\n}",
                    ],
                ],
            ],
            [
                'slug' => 'manage-api-keys',
                'title' => 'Manage API Keys and Rotation',
                'description' => 'Best practices for key scopes, expiration, and rotation policies.',
                'category' => 'security-privacy',
                'related_slugs' => ['setup-api-access', 'webhook-quickstart', 'billing-faq'],
                'sections' => [
                    [
                        'section_key' => 'least-privilege',
                        'title' => 'Use Least Privilege',
                        'paragraphs' => [
                            'Issue keys with minimal scopes for each service.',
                            'Separate keys by environment to prevent accidental cross-environment access.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'billing-faq',
                'title' => 'Billing FAQ',
                'description' => 'Answers to plan pricing, invoice access, and payment timing.',
                'category' => 'billing',
                'related_slugs' => ['refund-policy', 'setup-api-access', 'webhook-quickstart'],
                'sections' => [
                    [
                        'section_key' => 'invoice-timing',
                        'title' => 'When Are Invoices Issued?',
                        'paragraphs' => [
                            'Invoices are generated after successful payment capture.',
                            'You can find invoice metadata in your order history.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'refund-policy',
                'title' => 'Refund Policy',
                'description' => 'Review refund windows and how to request a refund.',
                'category' => 'billing',
                'related_slugs' => ['billing-faq', 'webhook-quickstart', 'setup-api-access'],
                'sections' => [
                    [
                        'section_key' => 'eligibility',
                        'title' => 'Refund Eligibility',
                        'paragraphs' => [
                            'Refund eligibility depends on your plan and purchase date.',
                            'Approved refunds are returned to the original payment method.',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($articles as $articleData) {
            /** @var HelpCategory $category */
            $category = $createdCategories->get($articleData['category']);

            $article = HelpArticle::query()->updateOrCreate(
                ['slug' => $articleData['slug']],
                [
                    'help_category_id' => $category->id,
                    'title' => $articleData['title'],
                    'description' => $articleData['description'],
                    'related_slugs' => $articleData['related_slugs'],
                    'is_published' => true,
                ]
            );

            $article->sections()->delete();

            foreach ($articleData['sections'] as $index => $section) {
                $article->sections()->create([
                    'section_key' => $section['section_key'],
                    'title' => $section['title'],
                    'paragraphs' => $section['paragraphs'],
                    'callout_type' => $section['callout_type'] ?? null,
                    'callout_title' => $section['callout_title'] ?? null,
                    'callout_content' => $section['callout_content'] ?? null,
                    'code_language' => $section['code_language'] ?? null,
                    'code' => $section['code'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
