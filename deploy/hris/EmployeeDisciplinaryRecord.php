<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDisciplinaryRecord extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'case_number',
        'case_type',
        'risk_level',
        'incident_date',
        'issued_date',
        'valid_until',
        'follow_up_date',
        'status',
        'reported_by',
        'handled_by',
        'approved_by',
        'attachment_reference',
        'summary',
        'corrective_action',
        'employee_response',
        'follow_up_result',
        'case_snapshot',
        'created_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'issued_date' => 'date',
        'valid_until' => 'date',
        'follow_up_date' => 'date',
        'case_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
