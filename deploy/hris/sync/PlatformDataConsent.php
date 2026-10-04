<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformDataConsent extends Model
{
    protected $fillable = [
        'platform_data_privacy_policy_id', 'company_id', 'subject_type', 'subject_reference',
        'subject_name', 'consent_purpose', 'consent_channel', 'status', 'granted_at',
        'revoked_at', 'expires_at', 'evidence_snapshot', 'notes', 'created_by',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
        'evidence_snapshot' => 'array',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(PlatformDataPrivacyPolicy::class, 'platform_data_privacy_policy_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
