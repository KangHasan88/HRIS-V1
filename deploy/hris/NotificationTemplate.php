<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'event_key',
        'channels',
        'subject',
        'body_template',
        'variables_schema',
        'is_enabled',
        'created_by',
    ];

    protected $casts = [
        'channels' => 'array',
        'variables_schema' => 'array',
        'is_enabled' => 'boolean',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
