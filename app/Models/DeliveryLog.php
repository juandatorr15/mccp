<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLog extends Model
{
    protected $fillable = [
        'message_id',
        'channel',
        'status',
        'payload',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
