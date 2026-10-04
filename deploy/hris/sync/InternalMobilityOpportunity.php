<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalMobilityOpportunity extends Model
{
    protected $fillable = [
        'job_position_id',
        'company_id',
        'branch_id',
        'opportunity_number',
        'title',
        'career_track',
        'movement_type',
        'readiness_requirement',
        'open_slots',
        'open_date',
        'close_date',
        'eligibility_note',
        'development_benefit',
        'owner_name',
        'status',
        'created_by',
    ];

    protected $casts = [
        'open_date' => 'date',
        'close_date' => 'date',
        'open_slots' => 'integer',
    ];

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(InternalMobilityApplication::class);
    }
}
