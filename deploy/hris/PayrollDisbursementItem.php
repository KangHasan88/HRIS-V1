<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDisbursementItem extends Model
{
    protected $fillable = [
        'payroll_disbursement_batch_id', 'payroll_payslip_id', 'employee_profile_id',
        'employee_number', 'employee_name', 'bank_name', 'bank_account_number',
        'bank_account_holder', 'amount', 'status', 'validation_message',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function batch(): BelongsTo { return $this->belongsTo(PayrollDisbursementBatch::class, 'payroll_disbursement_batch_id'); }
    public function payslip(): BelongsTo { return $this->belongsTo(PayrollPayslip::class, 'payroll_payslip_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id'); }
}
