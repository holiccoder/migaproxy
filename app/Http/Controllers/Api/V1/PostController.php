<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostIndexRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(PostIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $query = Post::query()
            ->with(['categories', 'tags'])
            ->published()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, function (Builder $query, string $category): void {
                $query->whereHas('categories', function (Builder $query) use ($category): void {
                    $query->where('slug', $category);
                });
            })
            ->when($filters['tag'] ?? null, function (Builder $query, string $tag): void {
                $query->whereHas('tags', function (Builder $query) use ($tag): void {
                    $query->where('slug', $tag);
                });
            });

        $posts = $query
            ->latest('published_at')
            ->paginate($filters['per_page'] ?? 15);

        return PostResource::collection($posts);
    }

    public function show(string $slug): PostResource
    {
        $post = Post::query()
            ->with(['categories', 'tags', 'seo'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return PostResource::make($post);
    }
}
