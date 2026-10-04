<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRunAnomaly extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'employee_profile_id',
        'anomaly_code',
        'anomaly_name',
        'severity',
        'previous_amount',
        'current_amount',
        'variance_percent',
        'status',
        'message',
        'evidence_payload',
    ];

    protected $casts = [
        'previous_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'variance_percent' => 'decimal:2',
        'evidence_payload' => 'array',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
    }
}
