<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerPerformanceNote extends Model
{
    protected $fillable = [
        'manager_user_id',
        'employee_profile_id',
        'note_date',
        'category',
        'sentiment',
        'visibility',
        'title',
        'note_body',
        'tags',
        'follow_up_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'note_date' => 'date',
        'follow_up_date' => 'date',
        'tags' => 'array',
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
