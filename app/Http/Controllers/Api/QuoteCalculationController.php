<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuoteCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteCalculationController extends Controller
{
    public function __construct(
        private QuoteCalculatorService $calculatorService
    ) {}

    /**
     * Get all active insurance types
     * Endpoint for chatbot to query available insurance types
     *
     * @return JsonResponse
     */
    public function getInsuranceTypes(): JsonResponse
    {
        $insuranceTypes = $this->calculatorService->getInsuranceTypes();

        return response()->json([
            'data' => $insuranceTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'description' => $type->description,
                    'requires_vehicle' => $type->requires_vehicle,
                ];
            }),
        ]);
    }

    /**
     * Get all active vehicle types
     * Can be filtered by insurance_type_id to show only relevant vehicles
     * Endpoint for chatbot to query available vehicle types
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getVehicleTypes(Request $request): JsonResponse
    {
        $insuranceTypeId = $request->query('insurance_type_id');

        if ($insuranceTypeId) {
            $vehicleTypes = $this->calculatorService->getVehicleTypesForInsurance($insuranceTypeId);
        } else {
            $vehicleTypes = $this->calculatorService->getVehicleTypes();
        }

        return response()->json([
            'data' => $vehicleTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'description' => $type->description,
                ];
            }),
        ]);
    }

    /**
     * Calculate quote cost
     * Endpoint for chatbot to calculate insurance cost before creating a quote
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function calculate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'insurance_type_id' => 'required|integer|exists:insurance_types,id',
            'asset_value' => 'required|numeric|min:0',
            'vehicle_type_id' => 'nullable|integer|exists:vehicle_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $calculation = $this->calculatorService->calculate(
                $request->insurance_type_id,
                $request->asset_value,
                $request->vehicle_type_id
            );

            return response()->json([
                'success' => true,
                'data' => $calculation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Quick quote calculation (simplified)
     * Alternative endpoint with simpler response format for chatbot
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function quickCalculate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'insurance_type_id' => 'required|integer|exists:insurance_types,id',
            'asset_value' => 'required|numeric|min:0',
            'vehicle_type_id' => 'nullable|integer|exists:vehicle_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $calculation = $this->calculatorService->calculate(
                $request->insurance_type_id,
                $request->asset_value,
                $request->vehicle_type_id
            );

            // Simplified response for chatbot
            return response()->json([
                'insurance_cost' => $calculation['calculated_cost'],
                'asset_value' => $calculation['asset_value'],
                'insurance_type' => $calculation['insurance_type']['name'],
                'vehicle_type' => $calculation['vehicle_type']['name'] ?? null,
                'currency' => 'MZN',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
