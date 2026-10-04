<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerReplacementRequisition extends Model
{
    protected $fillable = [
        'manager_user_id',
        'employee_profile_id',
        'company_id',
        'branch_id',
        'requisition_number',
        'position_title',
        'replacement_type',
        'vacancy_date',
        'target_join_date',
        'employment_type',
        'priority',
        'approval_status',
        'current_approver',
        'skill_requirements',
        'replacement_reason',
        'handover_notes',
        'created_by',
    ];

    protected $casts = [
        'vacancy_date' => 'date',
        'target_join_date' => 'date',
        'skill_requirements' => 'array',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
