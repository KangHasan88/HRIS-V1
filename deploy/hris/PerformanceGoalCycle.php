<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceGoalCycle extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'cycle_code',
        'cycle_name',
        'period_type',
        'period_start',
        'period_end',
        'checkin_deadline',
        'scoring_method',
        'status',
        'calibration_rules',
        'description',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'checkin_deadline' => 'date',
        'calibration_rules' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(PerformanceGoal::class);
    }
}
