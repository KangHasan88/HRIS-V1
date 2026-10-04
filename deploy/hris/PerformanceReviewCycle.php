<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceReviewCycle extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'cycle_code',
        'cycle_name',
        'review_type',
        'period_start',
        'period_end',
        'submission_deadline',
        'status',
        'competency_groups',
        'description',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'submission_deadline' => 'date',
        'competency_groups' => 'array',
    ];

    public function forms(): HasMany
    {
        return $this->hasMany(PerformanceReviewForm::class);
    }
}
