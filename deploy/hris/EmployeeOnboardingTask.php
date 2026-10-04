<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOnboardingTask extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'employee_onboarding_template_id',
        'task_code',
        'task_name',
        'category',
        'owner_role',
        'owner_name',
        'due_date',
        'completed_at',
        'status',
        'sort_order',
        'instruction',
        'completion_note',
        'assignment_snapshot',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
        'assignment_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmployeeOnboardingTemplate::class, 'employee_onboarding_template_id');
    }
}
