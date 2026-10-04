<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalWorkforcePieceOutput extends Model
{
    protected $fillable = [
        'external_workforce_piece_rate_id',
        'external_workforce_worker_id',
        'external_workforce_vendor_id',
        'external_workforce_contract_id',
        'company_id',
        'branch_id',
        'cost_center_id',
        'output_number',
        'work_date',
        'team_name',
        'quantity',
        'accepted_quantity',
        'rejected_quantity',
        'rate_per_unit',
        'gross_amount',
        'foreman_name',
        'approval_status',
        'approved_at',
        'handoff_target',
        'handoff_status',
        'evidence_reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'work_date' => 'date',
        'approved_at' => 'datetime',
        'quantity' => 'decimal:2',
        'accepted_quantity' => 'decimal:2',
        'rejected_quantity' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'gross_amount' => 'decimal:2',
    ];

    public function rate(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforcePieceRate::class, 'external_workforce_piece_rate_id');
    }

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
}
