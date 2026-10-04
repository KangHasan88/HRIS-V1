<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiometricConnector extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'connector_code',
        'connector_name',
        'vendor',
        'connection_type',
        'endpoint_url',
        'auth_mode',
        'agent_pairing_code',
        'agent_token_hash',
        'machine_code',
        'machine_location',
        'sync_interval_minutes',
        'last_sync_at',
        'next_sync_at',
        'agent_last_seen_at',
        'agent_last_ip',
        'agent_version',
        'agent_status',
        'agent_heartbeat_count',
        'status',
        'mapping_config',
        'agent_config',
        'agent_last_payload',
        'database_adapter_config',
        'adapter_status',
        'adapter_last_test_at',
        'adapter_last_preview',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'sync_interval_minutes' => 'integer',
        'last_sync_at' => 'datetime',
        'next_sync_at' => 'datetime',
        'agent_last_seen_at' => 'datetime',
        'agent_heartbeat_count' => 'integer',
        'mapping_config' => 'array',
        'agent_config' => 'array',
        'agent_last_payload' => 'array',
        'database_adapter_config' => 'array',
        'adapter_last_test_at' => 'datetime',
        'adapter_last_preview' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(BiometricSyncLog::class);
    }
}
