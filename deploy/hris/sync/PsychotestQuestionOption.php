<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychotestQuestionOption extends Model
{
    protected $fillable = [
        'psychotest_question_id',
        'option_code',
        'option_text',
        'score_value',
        'sort_order',
        'is_correct',
        'interpretation_note',
    ];

    protected $casts = [
        'score_value' => 'decimal:2',
        'sort_order' => 'integer',
        'is_correct' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(PsychotestQuestion::class, 'psychotest_question_id');
    }
}
