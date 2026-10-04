<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCalendarConnector extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'connector_code',
        'connector_name',
        'provider',
        'connection_type',
        'sender_email',
        'calendar_owner',
        'endpoint_url',
        'auth_mode',
        'default_timezone',
        'enabled_modules',
        'status',
        'last_sync_at',
        'mapping_config',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'enabled_modules' => 'array',
        'last_sync_at' => 'datetime',
        'mapping_config' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(EmailCalendarEvent::class);
    }
}
