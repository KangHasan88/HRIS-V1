<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentManpowerRequest extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'request_number',
        'position_title',
        'department',
        'employment_type',
        'headcount',
        'target_join_date',
        'priority',
        'request_reason',
        'budget_min',
        'budget_max',
        'job_summary',
        'requirements',
        'status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'approval_snapshot',
        'created_by',
    ];

    protected $casts = [
        'target_join_date' => 'date',
        'requested_at' => 'date',
        'approved_at' => 'datetime',
        'requirements' => 'array',
        'approval_snapshot' => 'array',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
