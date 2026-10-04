<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentityProviderSetting extends Model
{
    protected $fillable = [
        'company_id',
        'provider_code',
        'provider_name',
        'protocol',
        'domain_hint',
        'issuer_url',
        'sso_login_url',
        'sso_logout_url',
        'metadata_url',
        'client_id',
        'certificate_fingerprint',
        'claim_mapping',
        'role_mapping',
        'auto_provision',
        'jit_update_profile',
        'enforce_sso',
        'status',
        'last_tested_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'claim_mapping' => 'array',
        'role_mapping' => 'array',
        'auto_provision' => 'boolean',
        'jit_update_profile' => 'boolean',
        'enforce_sso' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
