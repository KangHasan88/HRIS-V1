<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPayslip extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'company_id',
        'payslip_number',
        'period',
        'status',
        'gross_pay',
        'total_deduction',
        'net_pay',
        'earning_snapshot',
        'deduction_snapshot',
        'employee_snapshot',
        'checksum',
        'published_at',
        'last_sent_at',
        'resend_count',
        'acknowledged_at',
        'acknowledged_by',
        'created_by',
    ];

    protected $casts = [
        'gross_pay' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'earning_snapshot' => 'array',
        'deduction_snapshot' => 'array',
        'employee_snapshot' => 'array',
        'published_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
