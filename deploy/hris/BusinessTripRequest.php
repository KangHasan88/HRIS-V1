<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTripRequest extends Model
{
    protected $fillable = [
        'request_number',
        'employee_profile_id',
        'start_date',
        'end_date',
        'destination_city',
        'destination_site',
        'trip_type',
        'transport_mode',
        'estimated_budget',
        'request_source',
        'approval_status',
        'current_approver',
        'submitted_at',
        'decided_at',
        'decided_by',
        'attachment_reference',
        'trip_snapshot',
        'purpose',
        'decision_notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_budget' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'trip_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
