<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkforcePlanningSimulation extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'simulation_number',
        'scenario_name',
        'department',
        'current_headcount',
        'required_headcount',
        'hire_headcount',
        'monthly_salary_budget',
        'overtime_hourly_rate',
        'overtime_hours_per_week',
        'planning_months',
        'hiring_cost',
        'training_cost',
        'hire_scenario_cost',
        'overtime_scenario_cost',
        'hybrid_scenario_cost',
        'recommended_scenario',
        'assumption_snapshot',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'current_headcount' => 'integer',
        'required_headcount' => 'integer',
        'hire_headcount' => 'integer',
        'monthly_salary_budget' => 'decimal:2',
        'overtime_hourly_rate' => 'decimal:2',
        'overtime_hours_per_week' => 'decimal:2',
        'planning_months' => 'integer',
        'hiring_cost' => 'decimal:2',
        'training_cost' => 'decimal:2',
        'hire_scenario_cost' => 'decimal:2',
        'overtime_scenario_cost' => 'decimal:2',
        'hybrid_scenario_cost' => 'decimal:2',
        'assumption_snapshot' => 'array',
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
