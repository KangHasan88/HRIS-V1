<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAccountingJournalLine extends Model
{
    protected $fillable = [
        'payroll_accounting_journal_batch_id', 'line_number', 'cost_center_code',
        'gl_account', 'gl_name', 'debit_credit', 'amount', 'currency',
        'employee_scope', 'description', 'source_snapshot',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'source_snapshot' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PayrollAccountingJournalBatch::class, 'payroll_accounting_journal_batch_id');
    }
}
