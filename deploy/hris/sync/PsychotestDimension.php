<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PsychotestDimension extends Model
{
    protected $fillable = [
        'psychotest_question_category_id',
        'dimension_code',
        'dimension_name',
        'scoring_direction',
        'weight_percent',
        'interpretation_note',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'weight_percent' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PsychotestQuestionCategory::class, 'psychotest_question_category_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(PsychotestQuestion::class);
    }
}
