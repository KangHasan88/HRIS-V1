<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeContract extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'parent_contract_id',
        'contract_number',
        'contract_type',
        'start_date',
        'end_date',
        'signed_at',
        'reminder_date',
        'renewal_sequence',
        'status',
        'document_reference',
        'approved_by',
        'notes',
        'contract_snapshot',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_at' => 'date',
        'reminder_date' => 'date',
        'renewal_sequence' => 'integer',
        'contract_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function parentContract(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_contract_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'parent_contract_id');
    }
}
