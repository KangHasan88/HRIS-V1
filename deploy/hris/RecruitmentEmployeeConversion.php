<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentEmployeeConversion extends Model
{
    protected $fillable = [
        'recruitment_candidate_id',
        'employee_profile_id',
        'conversion_number',
        'employee_number',
        'company_id',
        'branch_id',
        'employment_status',
        'position',
        'department',
        'grade',
        'cost_center',
        'join_date',
        'work_email',
        'conversion_status',
        'mapping_snapshot',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'join_date' => 'date',
        'mapping_snapshot' => 'array',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'recruitment_candidate_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
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
