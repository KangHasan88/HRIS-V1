<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoretaxSubmissionTracker extends Model
{
    protected $fillable = [
        'coretax_retry_log_id',
        'tax_monthly_export_template_id',
        'submission_number',
        'submission_status',
        'submitted_rows',
        'accepted_rows',
        'rejected_rows',
        'template_total_pph21',
        'acknowledged_total_pph21',
        'difference_amount',
        'reconciliation_snapshot',
        'status_history',
        'submitted_at',
        'last_checked_at',
        'created_by',
    ];

    protected $casts = [
        'template_total_pph21' => 'decimal:2',
        'acknowledged_total_pph21' => 'decimal:2',
        'difference_amount' => 'decimal:2',
        'reconciliation_snapshot' => 'array',
        'status_history' => 'array',
        'submitted_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    public function retryLog(): BelongsTo
    {
        return $this->belongsTo(CoretaxRetryLog::class, 'coretax_retry_log_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaxMonthlyExportTemplate::class, 'tax_monthly_export_template_id');
    }
}
