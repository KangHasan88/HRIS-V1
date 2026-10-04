<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingEnrollment extends Model
{
    protected $fillable = [
        'training_course_id',
        'employee_profile_id',
        'enrollment_number',
        'enrollment_status',
        'attendance_status',
        'attended_at',
        'certificate_number',
        'certificate_issued_at',
        'certificate_file_reference',
        'evaluation_score',
        'evaluation_feedback',
        'manager_notes',
        'created_by',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
        'certificate_issued_at' => 'datetime',
        'evaluation_score' => 'decimal:2',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
