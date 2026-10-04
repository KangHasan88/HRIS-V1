<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EssProfileUpdateRequest extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'request_number',
        'request_source',
        'current_snapshot',
        'requested_snapshot',
        'evidence_reference',
        'employee_note',
        'approval_status',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
        'review_note',
        'created_by',
    ];

    protected $casts = [
        'current_snapshot' => 'array',
        'requested_snapshot' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
