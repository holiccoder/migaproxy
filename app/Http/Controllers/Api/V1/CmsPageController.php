<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CmsPageIndexRequest;
use App\Http\Resources\CmsPageResource;
use App\Models\CmsPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CmsPageController extends Controller
{
    public function index(CmsPageIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $pages = CmsPage::query()
            ->published()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->paginate($filters['per_page'] ?? 15);

        return CmsPageResource::collection($pages);
    }

    public function show(string $slug): CmsPageResource
    {
        $page = CmsPage::query()
            ->with('seo')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return CmsPageResource::make($page);
    }
}
