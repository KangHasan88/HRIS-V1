<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PsychotestQuestion extends Model
{
    protected $fillable = [
        'psychotest_dimension_id',
        'question_code',
        'question_type',
        'question_text',
        'instruction',
        'time_limit_seconds',
        'difficulty_level',
        'version',
        'status',
        'scoring_key',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'time_limit_seconds' => 'integer',
        'version' => 'integer',
        'scoring_key' => 'array',
    ];

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(PsychotestDimension::class, 'psychotest_dimension_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PsychotestQuestionOption::class)->orderBy('sort_order');
    }
}
