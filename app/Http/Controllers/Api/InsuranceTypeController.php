<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InsuranceType;
use App\Services\QuoteCalculatorService;
use Illuminate\Http\JsonResponse;

class InsuranceTypeController extends Controller
{
    public function __construct(
        private QuoteCalculatorService $calculator
    ) {}

    /**
     * Get all insurance types
     */
    public function index(): JsonResponse
    {
        $insuranceTypes = InsuranceType::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $insuranceTypes]);
    }

    /**
     * Get insurance type details with requirements and insurers
     */
    public function showDetails(InsuranceType $insuranceType): JsonResponse
    {
        $insuranceType->load(['insurers' => function ($q) {
            $q->where('is_active', true)
                ->wherePivot('is_active', true)
                ->select(['id', 'name', 'email', 'phone']);
        }]);

        return response()->json([
            'id' => $insuranceType->id,
            'name' => $insuranceType->name,
            'slug' => $insuranceType->slug,
            'description' => $insuranceType->description,
            'requires_vehicle' => $insuranceType->requires_vehicle,
            'requirements' => $insuranceType->requirements,
            'insurers' => $insuranceType->insurers,
            'sample_pricing' => $this->getSamplePricing($insuranceType),
        ]);
    }

    /**
     * Get sample pricing for display on landing pages
     */
    private function getSamplePricing(InsuranceType $insuranceType): array
    {
        $sampleValues = [100000, 500000, 1000000]; // Sample asset values in MZN
        $results = [];

        foreach ($sampleValues as $value) {
            try {
                $calc = $this->calculator->calculateMultiInsurer(
                    $insuranceType->id,
                    $value
                );

                $insurers = $calc['insurers'] ?? [];

                $results[] = [
                    'asset_value' => $value,
                    'estimated_premium_range' => [
                        'min' => ! empty($insurers) ? $insurers[0]['calculated_cost'] : 0,
                        'max' => ! empty($insurers) ? end($insurers)['calculated_cost'] : 0,
                    ],
                ];
            } catch (\Exception $e) {
                // If calculation fails for this value, skip it
                continue;
            }
        }

        return $results;
    }
}
