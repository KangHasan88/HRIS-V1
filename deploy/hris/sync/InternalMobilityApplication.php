<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalMobilityApplication extends Model
{
    protected $fillable = [
        'internal_mobility_opportunity_id',
        'employee_profile_id',
        'succession_candidate_id',
        'individual_development_plan_id',
        'application_number',
        'source',
        'match_score',
        'readiness_level',
        'manager_support',
        'mobility_constraint',
        'motivation_note',
        'screening_note',
        'status',
        'target_movement_date',
        'owner_name',
        'created_by',
    ];

    protected $casts = [
        'match_score' => 'integer',
        'target_movement_date' => 'date',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(InternalMobilityOpportunity::class, 'internal_mobility_opportunity_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function successionCandidate(): BelongsTo
    {
        return $this->belongsTo(SuccessionCandidate::class);
    }

    public function developmentPlan(): BelongsTo
    {
        return $this->belongsTo(IndividualDevelopmentPlan::class, 'individual_development_plan_id');
    }
}
