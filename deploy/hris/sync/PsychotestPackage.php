<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PsychotestPackage extends Model
{
    protected $fillable = [
        'psychotest_provider_id',
        'package_code',
        'package_name',
        'assessment_category',
        'target_role_level',
        'estimated_minutes',
        'competency_mapping',
        'result_scale',
        'interpretation_method',
        'auto_pull_result',
        'requires_candidate_consent',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'competency_mapping' => 'array',
        'auto_pull_result' => 'boolean',
        'requires_candidate_consent' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PsychotestProvider::class, 'psychotest_provider_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(PsychotestInvitation::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(PsychotestResult::class);
    }

    public function batteryTemplates(): HasMany
    {
        return $this->hasMany(PsychotestBatteryTemplate::class);
    }
}
