<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeOffboardingCase extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'case_number',
        'reason_type',
        'notice_date',
        'last_working_date',
        'exit_interview_date',
        'status',
        'handled_by',
        'approved_by',
        'final_settlement_amount',
        'final_settlement_status',
        'reason_note',
        'exit_interview_summary',
        'handover_note',
        'employee_snapshot',
        'created_by',
    ];

    protected $casts = [
        'notice_date' => 'date',
        'last_working_date' => 'date',
        'exit_interview_date' => 'date',
        'final_settlement_amount' => 'decimal:2',
        'employee_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function clearanceItems(): HasMany
    {
        return $this->hasMany(EmployeeClearanceItem::class);
    }
}
