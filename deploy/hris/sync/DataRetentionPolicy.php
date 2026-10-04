<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataRetentionPolicy extends Model
{
    protected $fillable = [
        'company_id', 'policy_code', 'policy_name', 'data_domain', 'retention_years',
        'archive_strategy', 'disposal_action', 'requires_approval', 'legal_hold_override',
        'owner_role', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'legal_hold_override' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function legalHolds(): HasMany
    {
        return $this->hasMany(DataLegalHoldCase::class);
    }
}
