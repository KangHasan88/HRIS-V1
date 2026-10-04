<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeOnboardingTemplate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'role_scope',
        'department_scope',
        'company_id',
        'branch_id',
        'task_blueprint',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'task_blueprint' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class);
    }
}
