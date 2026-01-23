<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingRuleController extends Controller
{
    /**
     * Display a listing of pricing rules with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = PricingRule::with(['insuranceType', 'vehicleType', 'insurer']);

        // Filter by insurance type
        if ($request->has('insurance_type_id')) {
            $query->where('insurance_type_id', $request->insurance_type_id);
        }

        // Filter by vehicle type
        if ($request->has('vehicle_type_id')) {
            if ($request->vehicle_type_id === 'null') {
                $query->whereNull('vehicle_type_id');
            } else {
                $query->where('vehicle_type_id', $request->vehicle_type_id);
            }
        }

        // Filter by insurer
        if ($request->has('insurer_id')) {
            if ($request->insurer_id === 'null') {
                $query->whereNull('insurer_id');
            } else {
                $query->where('insurer_id', $request->insurer_id);
            }
        }

        // Filter by calculation type
        if ($request->has('calculation_type')) {
            $query->where('calculation_type', $request->calculation_type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        // Sort by priority (desc) and then by created_at
        $query->orderByDesc('priority')->latest();

        $pricingRules = $query->paginate($request->get('per_page', 20));

        return response()->json($pricingRules);
    }

    /**
     * Store a newly created pricing rule
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'insurance_type_id' => 'required|exists:insurance_types,id',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'insurer_id' => 'nullable|exists:insurers,id',
            'calculation_type' => 'required|in:percentage,fixed,tiered',
            'rate' => 'required|numeric|min:0',
            'price_multiplier' => 'nullable|numeric|min:0|max:10',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_amount' => 'nullable|numeric|min:0',
            'tiered_rates' => 'nullable|array',
            'tiered_rates.*.min' => 'required_with:tiered_rates|numeric|min:0',
            'tiered_rates.*.max' => 'required_with:tiered_rates|numeric|min:0',
            'tiered_rates.*.rate' => 'required_with:tiered_rates|numeric|min:0',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:0',
        ]);

        // Set defaults
        $validated['price_multiplier'] = $validated['price_multiplier'] ?? 1.00;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 0;

        $pricingRule = PricingRule::create($validated);

        return response()->json(
            $pricingRule->load(['insuranceType', 'vehicleType', 'insurer']),
            201
        );
    }

    /**
     * Display the specified pricing rule
     */
    public function show(PricingRule $pricingRule): JsonResponse
    {
        return response()->json(
            $pricingRule->load(['insuranceType', 'vehicleType', 'insurer'])
        );
    }

    /**
     * Update the specified pricing rule
     */
    public function update(Request $request, PricingRule $pricingRule): JsonResponse
    {
        $validated = $request->validate([
            'insurance_type_id' => 'sometimes|required|exists:insurance_types,id',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'insurer_id' => 'nullable|exists:insurers,id',
            'calculation_type' => 'sometimes|required|in:percentage,fixed,tiered',
            'rate' => 'sometimes|required|numeric|min:0',
            'price_multiplier' => 'nullable|numeric|min:0|max:10',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_amount' => 'nullable|numeric|min:0',
            'tiered_rates' => 'nullable|array',
            'tiered_rates.*.min' => 'required_with:tiered_rates|numeric|min:0',
            'tiered_rates.*.max' => 'required_with:tiered_rates|numeric|min:0',
            'tiered_rates.*.rate' => 'required_with:tiered_rates|numeric|min:0',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer|min:0',
        ]);

        $pricingRule->update($validated);

        return response()->json(
            $pricingRule->fresh(['insuranceType', 'vehicleType', 'insurer'])
        );
    }

    /**
     * Remove the specified pricing rule
     */
    public function destroy(PricingRule $pricingRule): JsonResponse
    {
        $pricingRule->delete();

        return response()->json(null, 204);
    }

    /**
     * Toggle active status of a pricing rule
     */
    public function toggleActive(PricingRule $pricingRule): JsonResponse
    {
        $pricingRule->update([
            'is_active' => !$pricingRule->is_active,
        ]);

        return response()->json(
            $pricingRule->fresh(['insuranceType', 'vehicleType', 'insurer'])
        );
    }

    /**
     * Duplicate a pricing rule
     */
    public function duplicate(PricingRule $pricingRule): JsonResponse
    {
        $newRule = $pricingRule->replicate();
        $newRule->priority = $pricingRule->priority - 1; // Lower priority
        $newRule->save();

        return response()->json(
            $newRule->load(['insuranceType', 'vehicleType', 'insurer']),
            201
        );
    }
}
