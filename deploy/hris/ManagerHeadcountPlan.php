<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerHeadcountPlan extends Model
{
    protected $fillable = [
        'manager_user_id',
        'company_id',
        'branch_id',
        'plan_number',
        'department',
        'position_title',
        'request_type',
        'current_headcount',
        'requested_headcount',
        'planning_period',
        'target_join_date',
        'priority',
        'budget_estimate',
        'budget_status',
        'approval_status',
        'current_approver',
        'business_reason',
        'risk_if_not_filled',
        'created_by',
    ];

    protected $casts = [
        'planning_period' => 'date',
        'target_join_date' => 'date',
        'budget_estimate' => 'decimal:2',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
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
