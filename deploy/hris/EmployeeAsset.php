<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAsset extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'asset_code',
        'asset_type',
        'asset_name',
        'serial_number',
        'asset_value',
        'assigned_at',
        'due_return_at',
        'returned_at',
        'assignment_status',
        'condition_out',
        'condition_in',
        'requires_clearance',
        'clearance_completed',
        'handover_by',
        'received_by',
        'notes',
        'asset_snapshot',
        'created_by',
    ];

    protected $casts = [
        'asset_value' => 'decimal:2',
        'assigned_at' => 'date',
        'due_return_at' => 'date',
        'returned_at' => 'date',
        'requires_clearance' => 'boolean',
        'clearance_completed' => 'boolean',
        'asset_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
