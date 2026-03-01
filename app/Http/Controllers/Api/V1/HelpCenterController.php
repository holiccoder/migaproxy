<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\HelpArticleIndexRequest;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class HelpCenterController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = HelpCategory::query()
            ->withCount(['articles as articles_count' => fn (Builder $query) => $query->where('is_published', true)])
            ->orderBy('title')
            ->get()
            ->map(function (HelpCategory $category): array {
                return [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'title' => $category->title,
                    'description' => $category->description,
                    'articles_count' => (int) $category->articles_count,
                ];
            });

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function popularSearches(): JsonResponse
    {
        return response()->json([
            'data' => ['Installation', 'Webhooks', 'Refund Policy', 'API', 'Billing'],
        ]);
    }

    public function articles(HelpArticleIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $articles = HelpArticle::query()
            ->with('category')
            ->where('is_published', true)
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, function (Builder $query, string $category): void {
                $query->whereHas('category', function (Builder $query) use ($category): void {
                    $query->where('slug', $category);
                });
            })
            ->orderBy('title')
            ->paginate($filters['per_page'] ?? 30);

        return response()->json([
            'data' => $articles->getCollection()->map(function (HelpArticle $article): array {
                return [
                    'id' => $article->id,
                    'slug' => $article->slug,
                    'title' => $article->title,
                    'description' => $article->description,
                    'category' => [
                        'slug' => $article->category?->slug,
                        'title' => $article->category?->title,
                    ],
                ];
            })->values(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = HelpArticle::query()
            ->with(['category', 'sections'])
            ->where('is_published', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedSlugs = is_array($article->related_slugs) ? $article->related_slugs : [];

        $relatedArticles = HelpArticle::query()
            ->whereIn('slug', $relatedSlugs)
            ->where('is_published', true)
            ->orderBy('title')
            ->get()
            ->map(function (HelpArticle $relatedArticle): array {
                return [
                    'slug' => $relatedArticle->slug,
                    'title' => $relatedArticle->title,
                    'description' => $relatedArticle->description,
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'id' => $article->id,
                'slug' => $article->slug,
                'title' => $article->title,
                'description' => $article->description,
                'category' => [
                    'slug' => $article->category?->slug,
                    'title' => $article->category?->title,
                ],
                'sections' => $article->sections->map(function ($section): array {
                    return [
                        'id' => $section->section_key,
                        'title' => $section->title,
                        'paragraphs' => $section->paragraphs ?? [],
                        'callout' => $section->callout_type && $section->callout_title && $section->callout_content
                            ? [
                                'type' => $section->callout_type,
                                'title' => $section->callout_title,
                                'content' => $section->callout_content,
                            ]
                            : null,
                        'codeBlock' => $section->code_language && $section->code
                            ? [
                                'language' => $section->code_language,
                                'code' => $section->code,
                            ]
                            : null,
                    ];
                })->values(),
                'related_articles' => $relatedArticles,
            ],
        ]);
    }
}
