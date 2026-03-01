<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpCategory extends Model
{
    /** @use HasFactory<\Database\Factories\HelpCategoryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'icon',
        'description',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(HelpArticle::class);
    }
}
