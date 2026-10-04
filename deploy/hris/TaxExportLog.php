<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxExportLog extends Model
{
    protected $fillable = [
        'company_id',
        'requested_by',
        'approved_by',
        'export_type',
        'scope',
        'period',
        'purpose',
        'status',
        'masked_fields',
        'approved_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'masked_fields' => 'array',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
