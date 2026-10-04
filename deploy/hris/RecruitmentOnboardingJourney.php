<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentOnboardingJourney extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'recruitment_employee_conversion_id',
        'journey_number',
        'buddy_name',
        'buddy_role',
        'buddy_contact',
        'start_date',
        'probation_end_date',
        'probation_reviewer',
        'journey_status',
        'checklist_snapshot',
        'probation_plan',
        'welcome_schedule',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'probation_end_date' => 'date',
        'checklist_snapshot' => 'array',
        'probation_plan' => 'array',
        'welcome_schedule' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function conversion(): BelongsTo
    {
        return $this->belongsTo(RecruitmentEmployeeConversion::class, 'recruitment_employee_conversion_id');
    }
}
