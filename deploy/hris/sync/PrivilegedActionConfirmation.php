<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegedActionConfirmation extends Model
{
    protected $fillable = [
        'user_id', 'action_key', 'action_label', 'resource_type', 'resource_reference',
        'confirmation_method', 'status', 'requested_at', 'confirmed_at', 'expires_at',
        'ip_address', 'user_agent', 'risk_snapshot', 'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'expires_at' => 'datetime',
        'risk_snapshot' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
