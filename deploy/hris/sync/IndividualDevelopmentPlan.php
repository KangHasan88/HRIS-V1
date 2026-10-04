<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualDevelopmentPlan extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'competency_role_requirement_id',
        'training_course_id',
        'plan_number',
        'development_area',
        'career_goal',
        'current_level',
        'target_level',
        'priority',
        'learning_recommendation',
        'action_plan',
        'start_date',
        'target_date',
        'mentor_name',
        'reviewer_name',
        'progress_percent',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'current_level' => 'integer',
        'target_level' => 'integer',
        'start_date' => 'date',
        'target_date' => 'date',
        'progress_percent' => 'integer',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function competencyRequirement(): BelongsTo
    {
        return $this->belongsTo(CompetencyRoleRequirement::class, 'competency_role_requirement_id');
    }

    public function trainingCourse(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class);
    }
}
