<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollDisbursementBatch extends Model
{
    protected $fillable = [
        'company_id', 'payroll_run_id', 'batch_number', 'period', 'bank_channel',
        'source_account', 'scheduled_payment_date', 'status', 'item_count',
        'total_amount', 'file_format', 'file_checksum', 'export_payload', 'notes', 'created_by',
    ];

    protected $casts = [
        'scheduled_payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'export_payload' => 'array',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function payrollRun(): BelongsTo { return $this->belongsTo(PayrollRun::class); }
    public function items(): HasMany { return $this->hasMany(PayrollDisbursementItem::class); }
    public function approvals(): HasMany { return $this->hasMany(PayrollDisbursementApproval::class); }
}
