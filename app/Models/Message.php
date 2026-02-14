<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = [
        'title',
        'content',
        'ai_summary',
        'status',
    ];

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(DeliveryLog::class);
    }
}
