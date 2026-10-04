<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollAccountingJournalBatch extends Model
{
    protected $fillable = [
        'accounting_erp_connector_id', 'company_id', 'payroll_run_id', 'journal_number',
        'period', 'posting_date', 'journal_type', 'status', 'line_count', 'debit_total',
        'credit_total', 'export_payload', 'payload_checksum', 'notes', 'created_by',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'debit_total' => 'decimal:2',
        'credit_total' => 'decimal:2',
        'export_payload' => 'array',
    ];

    public function connector(): BelongsTo { return $this->belongsTo(AccountingErpConnector::class, 'accounting_erp_connector_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function payrollRun(): BelongsTo { return $this->belongsTo(PayrollRun::class); }
    public function lines(): HasMany { return $this->hasMany(PayrollAccountingJournalLine::class); }
}
