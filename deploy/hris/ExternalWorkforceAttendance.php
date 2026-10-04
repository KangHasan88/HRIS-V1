<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalWorkforceAttendance extends Model
{
    protected $fillable = [
        'external_workforce_worker_id',
        'external_workforce_vendor_id',
        'external_workforce_contract_id',
        'company_id',
        'branch_id',
        'cost_center_id',
        'attendance_number',
        'attendance_date',
        'shift_code',
        'clock_in_at',
        'clock_out_at',
        'attendance_status',
        'mandays',
        'overtime_hours',
        'daily_rate',
        'overtime_rate',
        'gross_amount',
        'approval_status',
        'approved_by',
        'approved_at',
        'handoff_target',
        'handoff_status',
        'source_type',
        'evidence_reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'approved_at' => 'datetime',
        'mandays' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'gross_amount' => 'decimal:2',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceWorker::class, 'external_workforce_worker_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceVendor::class, 'external_workforce_vendor_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceContract::class, 'external_workforce_contract_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
