<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrafficHistory extends Model
{
    public const PROVIDER_IPMART = 'ipmart';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'success_count',
        'length',
        'request_date',
        'total_requests',
        'ipmart_id',
        'provider',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'success_count' => 'integer',
            'length' => 'decimal:6',
            'request_date' => 'datetime',
            'total_requests' => 'integer',
        ];
    }
}
