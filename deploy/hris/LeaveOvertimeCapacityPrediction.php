<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveOvertimeCapacityPrediction extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'prediction_number',
        'prediction_name',
        'department',
        'period_start',
        'period_end',
        'planned_headcount',
        'minimum_required_headcount',
        'existing_leave_days',
        'forecast_leave_days',
        'existing_overtime_hours',
        'forecast_overtime_hours',
        'estimated_capacity_days',
        'projected_available_days',
        'capacity_gap_days',
        'overtime_needed_hours',
        'risk_score',
        'risk_level',
        'recommendation_snapshot',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'existing_leave_days' => 'decimal:2',
        'forecast_leave_days' => 'decimal:2',
        'existing_overtime_hours' => 'decimal:2',
        'forecast_overtime_hours' => 'decimal:2',
        'estimated_capacity_days' => 'decimal:2',
        'projected_available_days' => 'decimal:2',
        'capacity_gap_days' => 'decimal:2',
        'overtime_needed_hours' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'recommendation_snapshot' => 'array',
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
