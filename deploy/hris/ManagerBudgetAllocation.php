<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerBudgetAllocation extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'manager_user_id',
        'allocation_number',
        'category',
        'period_start',
        'period_end',
        'allocated_amount',
        'alert_threshold_percent',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'allocated_amount' => 'decimal:2',
        'alert_threshold_percent' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }
}
