<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerOneOnOneLog extends Model
{
    protected $fillable = [
        'manager_user_id',
        'employee_profile_id',
        'meeting_date',
        'meeting_type',
        'mood',
        'topic_summary',
        'discussion_notes',
        'employee_feedback',
        'manager_commitment',
        'action_items',
        'next_meeting_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'next_meeting_date' => 'date',
        'action_items' => 'array',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
