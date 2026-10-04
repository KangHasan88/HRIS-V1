<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalWorkforceWorker extends Model
{
    protected $fillable = [
        'external_workforce_vendor_id',
        'external_workforce_contract_id',
        'company_id',
        'branch_id',
        'cost_center_id',
        'worker_number',
        'full_name',
        'worker_type',
        'identity_number',
        'phone',
        'position_title',
        'supervisor_name',
        'start_date',
        'end_date',
        'payment_handling',
        'daily_rate',
        'piece_rate',
        'document_status',
        'access_scope',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_rate' => 'decimal:2',
        'piece_rate' => 'decimal:2',
        'document_status' => 'array',
        'access_scope' => 'array',
    ];

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

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(ExternalWorkforceAttendance::class);
    }

    public function pieceOutputs(): HasMany
    {
        return $this->hasMany(ExternalWorkforcePieceOutput::class);
    }
}
