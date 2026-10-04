<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobGrade extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'level',
        'minimum_salary',
        'maximum_salary',
        'is_active',
    ];

    protected $casts = [
        'minimum_salary' => 'decimal:2',
        'maximum_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(JobPosition::class);
    }
}
