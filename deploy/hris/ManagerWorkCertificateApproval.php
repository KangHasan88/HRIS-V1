<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerWorkCertificateApproval extends Model
{
    protected $fillable = [
        'manager_user_id',
        'employee_profile_id',
        'certificate_number',
        'certificate_type',
        'recipient_name',
        'recipient_institution',
        'effective_date',
        'valid_until',
        'approval_status',
        'manager_decision',
        'decided_at',
        'certificate_purpose',
        'decision_note',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'valid_until' => 'date',
        'decided_at' => 'datetime',
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
