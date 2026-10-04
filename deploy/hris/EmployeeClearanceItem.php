<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeClearanceItem extends Model
{
    protected $fillable = [
        'employee_offboarding_case_id',
        'category',
        'item_name',
        'owner_role',
        'owner_name',
        'due_date',
        'completed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
    ];

    public function offboardingCase(): BelongsTo
    {
        return $this->belongsTo(EmployeeOffboardingCase::class, 'employee_offboarding_case_id');
    }
}
