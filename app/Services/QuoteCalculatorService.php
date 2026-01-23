<?php

namespace App\Services;

use App\Models\InsuranceType;
use App\Models\PricingRule;
use App\Models\VehicleType;
use Illuminate\Support\Facades\Cache;

class QuoteCalculatorService
{
    /**
     * Calculate insurance cost based on insurance type, vehicle type (optional), and asset value
     *
     * @param  int  $insuranceTypeId
     * @param  float  $assetValue
     * @param  int|null  $vehicleTypeId
     * @return array ['cost' => float, 'breakdown' => array]
     */
    public function calculate(int $insuranceTypeId, float $assetValue, ?int $vehicleTypeId = null): array
    {
        // Find applicable pricing rule
        $pricingRule = $this->findPricingRule($insuranceTypeId, $vehicleTypeId);

        if (! $pricingRule) {
            throw new \Exception('No pricing rule found for the selected insurance and vehicle type');
        }

        $cost = $pricingRule->calculate($assetValue);

        $insuranceType = InsuranceType::find($insuranceTypeId);
        $vehicleType = $vehicleTypeId ? VehicleType::find($vehicleTypeId) : null;

        return [
            'calculated_cost' => $cost,
            'asset_value' => $assetValue,
            'insurance_type' => [
                'id' => $insuranceType->id,
                'name' => $insuranceType->name,
            ],
            'vehicle_type' => $vehicleType ? [
                'id' => $vehicleType->id,
                'name' => $vehicleType->name,
            ] : null,
            'breakdown' => [
                'calculation_type' => $pricingRule->calculation_type,
                'rate' => $pricingRule->rate,
                'minimum_amount' => $pricingRule->minimum_amount,
                'maximum_amount' => $pricingRule->maximum_amount,
            ],
        ];
    }

    /**
     * Calculate rates from multiple insurers for comparison
     *
     * @param  int  $insuranceTypeId
     * @param  float  $assetValue
     * @param  int|null  $vehicleTypeId
     * @param  array  $additionalDetails
     * @return array
     */
    public function calculateMultiInsurer(
        int $insuranceTypeId,
        float $assetValue,
        ?int $vehicleTypeId = null,
        array $additionalDetails = []
    ): array {
        // Get insurance type with active insurers
        $insuranceType = InsuranceType::with(['insurers' => function ($q) {
            $q->where('is_active', true)
                ->wherePivot('is_active', true);
        }])->findOrFail($insuranceTypeId);

        $results = [];

        foreach ($insuranceType->insurers as $insurer) {
            // Find pricing rule (insurer-specific first, then global)
            $pricingRule = $this->findPricingRule($insuranceTypeId, $vehicleTypeId, $insurer->id);

            if (! $pricingRule) {
                continue;
            }

            // Calculate cost
            $cost = $pricingRule->calculate($assetValue);

            // Apply insurer-specific multiplier
            $cost *= ($pricingRule->price_multiplier ?? 1.00);

            $results[] = [
                'insurer_id' => $insurer->id,
                'insurer_name' => $insurer->name,
                'calculated_cost' => round($cost, 2),
                'turnaround_days' => $insurer->pivot->turnaround_days ?? 3,
                'pricing_rule_id' => $pricingRule->id,
                'calculation_breakdown' => [
                    'base_rate' => (float) $pricingRule->rate,
                    'calculation_type' => $pricingRule->calculation_type,
                    'multiplier' => (float) ($pricingRule->price_multiplier ?? 1.00),
                    'minimum_amount' => $pricingRule->minimum_amount ? (float) $pricingRule->minimum_amount : null,
                    'maximum_amount' => $pricingRule->maximum_amount ? (float) $pricingRule->maximum_amount : null,
                ],
            ];
        }

        // Sort by cost (lowest first)
        usort($results, fn ($a, $b) => $a['calculated_cost'] <=> $b['calculated_cost']);

        return [
            'insurance_type' => [
                'id' => $insuranceType->id,
                'name' => $insuranceType->name,
                'slug' => $insuranceType->slug,
            ],
            'asset_value' => $assetValue,
            'vehicle_type_id' => $vehicleTypeId,
            'insurers' => $results,
            'calculated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Find the most applicable pricing rule for the given parameters
     *
     * @param  int  $insuranceTypeId
     * @param  int|null  $vehicleTypeId
     * @param  int|null  $insurerId
     * @return PricingRule|null
     */
    private function findPricingRule(int $insuranceTypeId, ?int $vehicleTypeId = null, ?int $insurerId = null): ?PricingRule
    {
        // Try insurer-specific rule first
        if ($insurerId) {
            $rule = PricingRule::where('insurance_type_id', $insuranceTypeId)
                ->where('insurer_id', $insurerId)
                ->where('is_active', true)
                ->where(function ($q) use ($vehicleTypeId) {
                    if ($vehicleTypeId) {
                        $q->where('vehicle_type_id', $vehicleTypeId)
                            ->orWhereNull('vehicle_type_id');
                    } else {
                        $q->whereNull('vehicle_type_id');
                    }
                })
                ->orderByDesc('priority')
                ->first();

            if ($rule) {
                return $rule;
            }
        }

        // Fallback to global rule
        $query = PricingRule::where('insurance_type_id', $insuranceTypeId)
            ->whereNull('insurer_id')
            ->where('is_active', true)
            ->orderByDesc('priority');

        if ($vehicleTypeId) {
            // First try to find a rule specific to this vehicle type
            $rule = (clone $query)->where('vehicle_type_id', $vehicleTypeId)->first();

            if ($rule) {
                return $rule;
            }

            // If no specific rule, try to find a general rule (null vehicle type)
            return $query->whereNull('vehicle_type_id')->first();
        }

        // No vehicle type specified, find general rule
        return $query->whereNull('vehicle_type_id')->first();
    }

    /**
     * Get all active insurance types
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInsuranceTypes()
    {
        return Cache::remember('insurance_types_active', 3600, function () {
            return InsuranceType::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get all active vehicle types
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVehicleTypes()
    {
        return Cache::remember('vehicle_types_active', 3600, function () {
            return VehicleType::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get vehicle types for a specific insurance type
     *
     * @param  int  $insuranceTypeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVehicleTypesForInsurance(int $insuranceTypeId)
    {
        $insuranceType = InsuranceType::find($insuranceTypeId);

        if (! $insuranceType || ! $insuranceType->requires_vehicle) {
            return collect([]);
        }

        // Get vehicle types that have pricing rules for this insurance type
        return VehicleType::active()
            ->whereHas('pricingRules', function ($query) use ($insuranceTypeId) {
                $query->where('insurance_type_id', $insuranceTypeId)
                    ->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
