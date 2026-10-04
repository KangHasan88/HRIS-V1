<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuccessionPlan extends Model
{
    protected $fillable = [
        'job_position_id',
        'incumbent_employee_profile_id',
        'plan_number',
        'criticality',
        'vacancy_risk',
        'target_ready_by',
        'business_impact',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_ready_by' => 'date',
    ];

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function incumbent(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'incumbent_employee_profile_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(SuccessionCandidate::class);
    }
}
