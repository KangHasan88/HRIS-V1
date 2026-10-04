<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeMovement extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'movement_number',
        'movement_type',
        'request_date',
        'effective_date',
        'status',
        'from_company_id',
        'to_company_id',
        'from_branch_id',
        'to_branch_id',
        'from_position',
        'to_position',
        'from_department',
        'to_department',
        'from_grade',
        'to_grade',
        'from_cost_center',
        'to_cost_center',
        'reason',
        'approved_by',
        'approval_notes',
        'notes',
        'before_snapshot',
        'after_snapshot',
        'created_by',
    ];

    protected $casts = [
        'request_date' => 'date',
        'effective_date' => 'date',
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function fromCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    public function toCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }
}
