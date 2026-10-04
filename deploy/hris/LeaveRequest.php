<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'request_number',
        'employee_profile_id',
        'leave_policy_type_id',
        'leave_policy_rule_id',
        'start_date',
        'end_date',
        'requested_days',
        'balance_before',
        'balance_after',
        'duration_unit',
        'request_source',
        'approval_status',
        'current_approver',
        'submitted_at',
        'decided_at',
        'decided_by',
        'attachment_reference',
        'simulation_snapshot',
        'reason',
        'decision_notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'requested_days' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'simulation_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function policyType(): BelongsTo
    {
        return $this->belongsTo(LeavePolicyType::class, 'leave_policy_type_id');
    }

    public function policyRule(): BelongsTo
    {
        return $this->belongsTo(LeavePolicyRule::class, 'leave_policy_rule_id');
    }
}
