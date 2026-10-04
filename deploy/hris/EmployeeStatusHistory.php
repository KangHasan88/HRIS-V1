<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeStatusHistory extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'from_status',
        'to_status',
        'effective_date',
        'review_date',
        'contract_end_date',
        'reason',
        'approved_by',
        'notes',
        'status_snapshot',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'review_date' => 'date',
        'contract_end_date' => 'date',
        'status_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
