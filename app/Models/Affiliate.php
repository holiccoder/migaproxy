<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    /** @use HasFactory<\Database\Factories\AffiliateFactory> */
    use HasFactory;

    public const COMMISSION_TYPE_PERCENTAGE = 'percentage';

    public const COMMISSION_TYPE_FIXED = 'fixed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'code',
        'commission_type',
        'commission_value',
        'cookie_days',
        'is_active',
        'total_earnings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_value' => 'integer',
            'cookie_days' => 'integer',
            'is_active' => 'boolean',
            'total_earnings' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function calculateCommission(int $amount): int
    {
        if ($this->commission_type === self::COMMISSION_TYPE_FIXED) {
            return min($amount, max(0, $this->commission_value));
        }

        $percentage = min(100, max(0, $this->commission_value));

        return (int) floor($amount * ($percentage / 100));
    }
}
