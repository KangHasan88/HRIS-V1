<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceImportBatch extends Model
{
    protected $fillable = [
        'batch_number',
        'company_id',
        'branch_id',
        'source_type',
        'machine_code',
        'machine_name',
        'import_period_start',
        'import_period_end',
        'file_reference',
        'imported_rows',
        'matched_rows',
        'anomaly_rows',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'import_period_start' => 'date',
        'import_period_end' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(AttendanceImportRow::class);
    }
}
