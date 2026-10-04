<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingErpConnector extends Model
{
    protected $fillable = [
        'company_id', 'connector_code', 'connector_name', 'erp_system', 'connection_type',
        'base_url', 'auth_mode', 'credential_reference', 'gl_mapping', 'cost_center_mapping',
        'journal_mode', 'status', 'last_posted_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'gl_mapping' => 'array',
        'cost_center_mapping' => 'array',
        'last_posted_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function journals(): HasMany { return $this->hasMany(PayrollAccountingJournalBatch::class); }
}
