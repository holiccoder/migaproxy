<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'status' => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
            'seo' => $this->whenLoaded('seo', function (): array {
                $title = $this->seo->title ?? $this->meta_title ?? $this->title;
                $description = $this->seo->description ?? $this->meta_description ?? $this->excerpt;
                $image = $this->seo->image;
                $canonicalUrl = $this->seo->canonical_url;

                return [
                    'title' => $title,
                    'description' => $description,
                    'image' => $image,
                    'author' => $this->seo->author,
                    'robots' => $this->seo->robots,
                    'canonical_url' => $canonicalUrl,
                    'open_graph' => [
                        'title' => $title,
                        'description' => $description,
                        'image' => $image,
                        'url' => $canonicalUrl,
                        'type' => 'website',
                    ],
                    'twitter_card' => [
                        'card' => filled($image) ? 'summary_large_image' : 'summary',
                        'title' => $title,
                        'description' => $description,
                        'image' => $image,
                    ],
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
