<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RalphJSmit\Laravel\SEO\Support\HasSEO;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    use HasSEO;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'published_at',
        'cover_image_path',
        'is_pinned_to_top',
        'is_featured_in_homepage',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_pinned_to_top' => 'boolean',
            'is_featured_in_homepage' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at?->isPast() === true;
    }
}
