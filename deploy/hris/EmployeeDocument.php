<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'parent_document_id',
        'document_number',
        'document_type',
        'title',
        'version',
        'status',
        'issued_at',
        'expired_at',
        'reminder_date',
        'file_reference',
        'storage_path',
        'ocr_status',
        'ocr_text',
        'metadata',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'issued_at' => 'date',
        'expired_at' => 'date',
        'reminder_date' => 'date',
        'metadata' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_document_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_document_id');
    }
}
