<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competency extends Model
{
    protected $fillable = [
        'company_id',
        'competency_code',
        'competency_name',
        'category',
        'description',
        'proficiency_descriptors',
        'is_core',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'proficiency_descriptors' => 'array',
        'is_core' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(CompetencyRoleRequirement::class);
    }
}
