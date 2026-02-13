<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceHistory extends Model
{
    /** @use HasFactory<\Database\Factories\BalanceHistoryFactory> */
    use HasFactory;

    public const TYPE_CREDIT = 'credit';

    public const TYPE_DEBIT = 'debit';

    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'before_balance',
        'after_balance',
        'reference',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'before_balance' => 'integer',
            'after_balance' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
