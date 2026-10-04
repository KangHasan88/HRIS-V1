<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'request_number',
        'employee_profile_id',
        'overtime_date',
        'start_at',
        'end_at',
        'requested_hours',
        'approved_hours',
        'payroll_multiplier',
        'hourly_rate',
        'estimated_amount',
        'overtime_type',
        'request_source',
        'approval_status',
        'current_approver',
        'submitted_at',
        'decided_at',
        'decided_by',
        'handoff_to_payroll',
        'payroll_handoff_at',
        'payroll_component_code',
        'calculation_snapshot',
        'reason',
        'decision_notes',
        'created_by',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'requested_hours' => 'decimal:2',
        'approved_hours' => 'decimal:2',
        'payroll_multiplier' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'estimated_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'handoff_to_payroll' => 'boolean',
        'payroll_handoff_at' => 'datetime',
        'calculation_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
