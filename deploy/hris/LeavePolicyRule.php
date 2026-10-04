<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavePolicyRule extends Model
{
    protected $fillable = [
        'leave_policy_type_id',
        'company_id',
        'branch_id',
        'employment_status',
        'effective_start',
        'effective_end',
        'accrual_frequency',
        'accrual_days',
        'reset_month',
        'include_holidays',
        'include_weekends',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'effective_start' => 'date',
        'effective_end' => 'date',
        'accrual_days' => 'decimal:2',
        'include_holidays' => 'boolean',
        'include_weekends' => 'boolean',
    ];

    public function policyType(): BelongsTo
    {
        return $this->belongsTo(LeavePolicyType::class, 'leave_policy_type_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
