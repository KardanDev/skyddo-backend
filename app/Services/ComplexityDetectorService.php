<?php

namespace App\Services;

class ComplexityDetectorService
{
    private const HIGH_VALUE_THRESHOLD = 2000000; // 2M MZN
    private const MODERATE_VALUE_THRESHOLD = 500000; // 500K MZN

    /**
     * Analyze quote data and determine complexity level
     *
     * @param  array  $quoteData
     * @return array ['complexity_level', 'complexity_score', 'complexity_factors', 'requires_agent_review']
     */
    public function analyze(array $quoteData): array
    {
        $factors = [];
        $score = 0;

        // Factor 1: Asset value
        $assetValue = $quoteData['asset_value'] ?? 0;
        if ($assetValue > self::HIGH_VALUE_THRESHOLD) {
            $factors['high_value'] = true;
            $score += 30;
        } elseif ($assetValue > self::MODERATE_VALUE_THRESHOLD) {
            $factors['moderate_value'] = true;
            $score += 15;
        }

        // Factor 2: Number of additional requirements
        $additionalDetails = $quoteData['additional_details'] ?? [];
        $requirementsCount = count(array_filter($additionalDetails));
        if ($requirementsCount > 5) {
            $factors['many_requirements'] = true;
            $score += 20;
        } elseif ($requirementsCount > 3) {
            $factors['moderate_requirements'] = true;
            $score += 10;
        }

        // Factor 3: Insurance type complexity
        $complexInsuranceTypes = ['maritimo', 'propriedade'];
        $insuranceTypeSlug = $quoteData['insurance_type_slug'] ?? '';
        if (in_array($insuranceTypeSlug, $complexInsuranceTypes)) {
            $factors['complex_insurance_type'] = true;
            $score += 25;
        }

        // Factor 4: Multiple coverage types or add-ons
        if (isset($additionalDetails['add_ons']) && is_array($additionalDetails['add_ons']) && count($additionalDetails['add_ons']) > 2) {
            $factors['multiple_addons'] = true;
            $score += 15;
        }

        // Factor 5: Special requirements or conditions
        $specialFields = ['pre_existing_conditions', 'special_requirements', 'hazardous_materials', 'unusual_route'];
        foreach ($specialFields as $field) {
            if (! empty($additionalDetails[$field])) {
                $factors['special_requirements'] = true;
                $score += 10;
                break;
            }
        }

        // Determine complexity level based on score
        $level = match (true) {
            $score >= 50 => 'complex',
            $score >= 25 => 'moderate',
            default => 'simple',
        };

        return [
            'complexity_level' => $level,
            'complexity_score' => $score,
            'complexity_factors' => $factors,
            'requires_agent_review' => $level === 'complex',
        ];
    }
}
