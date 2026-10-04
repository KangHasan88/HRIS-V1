<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLoanInstallment extends Model
{
    protected $fillable = [
        'employee_loan_id', 'installment_no', 'payroll_period', 'due_date',
        'scheduled_amount', 'paid_amount', 'status', 'posted_at', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'posted_at' => 'datetime',
        'scheduled_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id');
    }
}
