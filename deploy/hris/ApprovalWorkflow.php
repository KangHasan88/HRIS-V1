<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'module_key',
        'trigger_event',
        'status',
        'is_enabled',
        'condition_schema',
        'fallback_route',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'condition_schema' => 'array',
        'fallback_route' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalWorkflowStep::class)->orderBy('step_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
