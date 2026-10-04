<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuccessionCandidate extends Model
{
    protected $fillable = [
        'succession_plan_id',
        'employee_profile_id',
        'individual_development_plan_id',
        'candidate_number',
        'readiness_level',
        'readiness_score',
        'performance_rating',
        'potential_rating',
        'mobility_preference',
        'retention_risk',
        'development_gap',
        'next_action',
        'owner_name',
        'review_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'readiness_score' => 'integer',
        'review_date' => 'date',
    ];

    public function successionPlan(): BelongsTo
    {
        return $this->belongsTo(SuccessionPlan::class);
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function developmentPlan(): BelongsTo
    {
        return $this->belongsTo(IndividualDevelopmentPlan::class, 'individual_development_plan_id');
    }
}
