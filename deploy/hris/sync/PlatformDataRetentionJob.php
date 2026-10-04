<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformDataRetentionJob extends Model
{
    protected $fillable = [
        'platform_data_privacy_policy_id', 'company_id', 'job_number', 'data_domain',
        'scope_reference', 'cutoff_date', 'action_type', 'legal_hold', 'estimated_records',
        'status', 'scheduled_at', 'completed_at', 'impact_snapshot', 'notes', 'created_by',
    ];

    protected $casts = [
        'cutoff_date' => 'date',
        'legal_hold' => 'boolean',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'impact_snapshot' => 'array',
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
