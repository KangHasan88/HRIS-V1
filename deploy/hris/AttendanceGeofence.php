<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceGeofence extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'location_type',
        'latitude',
        'longitude',
        'radius_meter',
        'address',
        'allow_clock_in',
        'allow_clock_out',
        'requires_photo',
        'requires_liveness',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'allow_clock_in' => 'boolean',
        'allow_clock_out' => 'boolean',
        'requires_photo' => 'boolean',
        'requires_liveness' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(AttendanceLocationPolicy::class);
    }
}
