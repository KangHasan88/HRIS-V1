<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyRoleRequirement extends Model
{
    protected $fillable = [
        'competency_id',
        'job_position_id',
        'job_grade_id',
        'training_course_id',
        'requirement_number',
        'career_path',
        'required_level',
        'target_level',
        'weight',
        'criticality',
        'assessment_method',
        'evidence_examples',
        'development_action',
        'status',
        'created_by',
    ];

    protected $casts = [
        'required_level' => 'integer',
        'target_level' => 'integer',
        'weight' => 'decimal:2',
        'evidence_examples' => 'array',
    ];

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    public function trainingCourse(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class);
    }
}
