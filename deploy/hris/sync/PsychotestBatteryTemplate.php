<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychotestBatteryTemplate extends Model
{
    protected $fillable = [
        'psychotest_package_id',
        'template_code',
        'template_name',
        'target_role_level',
        'total_duration_minutes',
        'section_config',
        'version',
        'status',
        'instruction',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_duration_minutes' => 'integer',
        'section_config' => 'array',
        'version' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(PsychotestPackage::class, 'psychotest_package_id');
    }
}
