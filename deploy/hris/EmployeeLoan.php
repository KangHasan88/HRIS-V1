<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'employee_profile_id', 'company_id', 'branch_id', 'loan_number', 'loan_type',
        'provider', 'loan_date', 'principal_amount', 'admin_fee', 'interest_amount',
        'tenor_months', 'monthly_installment', 'paid_amount', 'outstanding_amount',
        'deduction_component_code', 'payroll_posting_status', 'approval_status',
        'current_approver', 'start_period', 'end_period', 'notes', 'created_by',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'start_period' => 'date',
        'end_period' => 'date',
        'principal_amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

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

    public function installments(): HasMany
    {
        return $this->hasMany(EmployeeLoanInstallment::class);
    }
}
