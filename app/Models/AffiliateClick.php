<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClick extends Model
{
    /** @use HasFactory<\Database\Factories\AffiliateClickFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'affiliate_id',
        'code',
        'ip',
        'user_agent',
        'referrer',
        'landing_url',
        'clicked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }
}
