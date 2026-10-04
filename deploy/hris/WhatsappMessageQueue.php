<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappMessageQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'whatsapp_gateway_setting_id',
        'message_number',
        'event_key',
        'source_type',
        'source_id',
        'recipient_name',
        'recipient_phone',
        'template_name',
        'message_body',
        'priority',
        'status',
        'scheduled_at',
        'sent_at',
        'attempt_count',
        'payload_snapshot',
        'provider_response',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'payload_snapshot' => 'array',
        'provider_response' => 'array',
    ];

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(WhatsappGatewaySetting::class, 'whatsapp_gateway_setting_id');
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WhatsappWebhookLog::class);
    }
}
