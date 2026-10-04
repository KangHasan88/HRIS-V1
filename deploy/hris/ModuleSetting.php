<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleSetting extends Model
{
    protected $fillable = [
        'code',
        'name',
        'group',
        'description',
        'platform',
        'is_enabled',
        'is_core',
        'progress',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_core' => 'boolean',
            'progress' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function companySettings(): HasMany
    {
        return $this->hasMany(CompanyModuleSetting::class);
    }
}
