<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoretaxConnectorSetting extends Model
{
    protected $fillable = [
        'company_id',
        'environment',
        'base_url',
        'client_id',
        'secret_mask',
        'credential_hash',
        'timeout_seconds',
        'max_retry',
        'is_active',
        'last_checked_at',
        'last_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function retryLogs(): HasMany
    {
        return $this->hasMany(CoretaxRetryLog::class);
    }
}
