<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'requires_vehicle',
        'requirements',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_vehicle' => 'boolean',
            'is_active' => 'boolean',
            'requirements' => 'array',
        ];
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function insurers(): BelongsToMany
    {
        return $this->belongsToMany(Insurer::class, 'insurer_insurance_type')
            ->withPivot('is_active', 'turnaround_days')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
