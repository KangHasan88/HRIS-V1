<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappGatewaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'gateway_code',
        'gateway_name',
        'provider',
        'connection_type',
        'endpoint_url',
        'sender_id',
        'auth_mode',
        'status',
        'enabled_events',
        'credential_snapshot',
        'last_ping_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'enabled_events' => 'array',
        'credential_snapshot' => 'array',
        'last_ping_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessageQueue::class);
    }
}
