<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PsychotestQuestionCategory extends Model
{
    protected $fillable = [
        'category_code',
        'category_name',
        'assessment_domain',
        'owner_name',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dimensions(): HasMany
    {
        return $this->hasMany(PsychotestDimension::class);
    }
}
