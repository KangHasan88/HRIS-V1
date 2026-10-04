<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'whatsapp_gateway_setting_id',
        'whatsapp_message_queue_id',
        'webhook_number',
        'event_type',
        'provider_message_id',
        'delivery_status',
        'payload',
        'received_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
    ];

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(WhatsappGatewaySetting::class, 'whatsapp_gateway_setting_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(WhatsappMessageQueue::class, 'whatsapp_message_queue_id');
    }
}
