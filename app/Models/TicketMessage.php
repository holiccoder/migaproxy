<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    /** @use HasFactory<\Database\Factories\TicketMessageFactory> */
    use HasFactory;

    public const SENDER_USER = 'user';

    public const SENDER_ADMIN = 'admin';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'admin_id',
        'sender_type',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
