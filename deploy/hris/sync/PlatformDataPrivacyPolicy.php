<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformDataPrivacyPolicy extends Model
{
    protected $fillable = [
        'company_id', 'policy_code', 'policy_name', 'data_domain', 'masked_fields',
        'restricted_roles', 'masking_mode', 'consent_required', 'retention_years',
        'legal_hold_enabled', 'legal_basis', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'masked_fields' => 'array',
        'restricted_roles' => 'array',
        'consent_required' => 'boolean',
        'legal_hold_enabled' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(PlatformDataConsent::class);
    }

    public function retentionJobs(): HasMany
    {
        return $this->hasMany(PlatformDataRetentionJob::class);
    }
}
