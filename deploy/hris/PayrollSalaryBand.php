<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollSalaryBand extends Model
{
    protected $fillable = [
        'company_id',
        'job_grade_id',
        'band_code',
        'band_name',
        'level',
        'minimum_salary',
        'midpoint_salary',
        'maximum_salary',
        'currency',
        'effective_start',
        'effective_end',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'level' => 'integer',
        'minimum_salary' => 'decimal:2',
        'midpoint_salary' => 'decimal:2',
        'maximum_salary' => 'decimal:2',
        'effective_start' => 'date',
        'effective_end' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    public function salaryAssignments(): HasMany
    {
        return $this->hasMany(EmployeeSalaryAssignment::class);
    }
}
