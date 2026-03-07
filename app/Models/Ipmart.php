<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ipmart extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'ipmart_id',
        'available_traffic',
        'ipmart_email',
        'plan_balance',
        'proxyName',
        'proxyPwd',
        'login_name',
        'passwd',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'available_traffic' => 'integer',
            'plan_balance' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
