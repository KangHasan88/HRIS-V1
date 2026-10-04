<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    protected $fillable = [
        'notification_template_id',
        'user_id',
        'event_key',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'channels',
        'priority',
        'status',
        'subject',
        'rendered_message',
        'payload_snapshot',
        'delivery_log',
        'scheduled_at',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'channels' => 'array',
        'payload_snapshot' => 'array',
        'delivery_log' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
