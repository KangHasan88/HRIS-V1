<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EssTrustedDevice extends Model
{
    protected $fillable = [
        'user_id',
        'employee_profile_id',
        'device_uid',
        'device_name',
        'platform',
        'app_version',
        'biometric_enabled',
        'biometric_type',
        'fallback_pin_hash',
        'trust_status',
        'trusted_at',
        'last_authenticated_at',
        'failed_attempts',
        'risk_flags',
        'capability_snapshot',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'biometric_enabled' => 'boolean',
        'trusted_at' => 'datetime',
        'last_authenticated_at' => 'datetime',
        'risk_flags' => 'array',
        'capability_snapshot' => 'array',
    ];

    protected $hidden = [
        'fallback_pin_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
