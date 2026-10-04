<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryAssignment extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'payroll_salary_band_id',
        'job_position_id',
        'base_salary',
        'fixed_allowance',
        'variable_allowance',
        'payroll_frequency',
        'effective_start',
        'effective_end',
        'approval_status',
        'confidentiality_level',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'fixed_allowance' => 'decimal:2',
        'variable_allowance' => 'decimal:2',
        'effective_start' => 'date',
        'effective_end' => 'date',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function salaryBand(): BelongsTo
    {
        return $this->belongsTo(PayrollSalaryBand::class, 'payroll_salary_band_id');
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }
}
