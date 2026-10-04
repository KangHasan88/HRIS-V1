<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAdjustment extends Model
{
    protected $fillable = [
        'employee_profile_id', 'company_id', 'payroll_run_id', 'adjustment_number',
        'adjustment_type', 'component_code', 'component_name', 'component_direction',
        'period_from', 'period_to', 'posting_period', 'base_amount', 'adjustment_amount',
        'taxable_amount', 'tax_treatment', 'status', 'current_approver', 'approved_at',
        'approved_by', 'posted_at', 'impact_snapshot', 'reason', 'audit_notes', 'created_by',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'impact_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }
}
