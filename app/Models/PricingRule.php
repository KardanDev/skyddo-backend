<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $fillable = [
        'insurance_type_id',
        'vehicle_type_id',
        'insurer_id',
        'calculation_type',
        'rate',
        'price_multiplier',
        'minimum_amount',
        'maximum_amount',
        'tiered_rates',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'price_multiplier' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'tiered_rates' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function insuranceType(): BelongsTo
    {
        return $this->belongsTo(InsuranceType::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate insurance cost based on asset value
     */
    public function calculate(float $assetValue): float
    {
        if ($this->calculation_type === 'fixed') {
            $cost = $this->rate;
        } elseif ($this->calculation_type === 'percentage') {
            $cost = $assetValue * $this->rate;
        } elseif ($this->calculation_type === 'tiered') {
            $cost = $this->calculateTiered($assetValue);
        } else {
            $cost = 0;
        }

        // Apply min/max constraints
        if ($this->minimum_amount && $cost < $this->minimum_amount) {
            $cost = $this->minimum_amount;
        }

        if ($this->maximum_amount && $cost > $this->maximum_amount) {
            $cost = $this->maximum_amount;
        }

        return round($cost, 2);
    }

    /**
     * Calculate tiered pricing based on value ranges
     */
    private function calculateTiered(float $assetValue): float
    {
        if (! $this->tiered_rates || ! is_array($this->tiered_rates)) {
            return 0;
        }

        // Tiered rates format: [{"min": 0, "max": 10000, "rate": 0.05}, {"min": 10001, "max": 50000, "rate": 0.03}]
        foreach ($this->tiered_rates as $tier) {
            $min = $tier['min'] ?? 0;
            $max = $tier['max'] ?? PHP_FLOAT_MAX;
            $rate = $tier['rate'] ?? 0;

            if ($assetValue >= $min && $assetValue <= $max) {
                return $assetValue * $rate;
            }
        }

        return 0;
    }
}
