<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanySettingController extends Controller
{
    /**
     * Constructor - Apply middleware
     */
    public function __construct()
    {
        // Only admins can update company settings
        $this->middleware(function ($request, $next) {
            if (!in_array($request->method(), ['GET', 'HEAD'])) {
                $user = $request->user();
                if (!$user || !in_array($user->role, ['super_user', 'admin'])) {
                    return response()->json([
                        'message' => 'You do not have permission to edit company settings. Only administrators can perform this action.',
                    ], 403);
                }
            }
            return $next($request);
        });
    }

    /**
     * Get company settings
     */
    public function show(): JsonResponse
    {
        $settings = CompanySetting::current();

        return response()->json([
            'id' => $settings->id,
            'company_name' => $settings->company_name,
            'company_email' => $settings->company_email,
            'company_phone' => $settings->company_phone,
            'company_address' => $settings->company_address,
            'website' => $settings->website,
            'tax_id' => $settings->tax_id,
            'description' => $settings->description,
            'logo_path' => $settings->logo_path,
            'logo_url' => $settings->logo_url,
            'created_at' => $settings->created_at,
            'updated_at' => $settings->updated_at,
        ]);
    }

    /**
     * Update company settings
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'tax_id' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = CompanySetting::current();
        $settings->update($validator->validated());

        return response()->json([
            'message' => 'Company settings updated successfully',
            'data' => [
                'id' => $settings->id,
                'company_name' => $settings->company_name,
                'company_email' => $settings->company_email,
                'company_phone' => $settings->company_phone,
                'company_address' => $settings->company_address,
                'website' => $settings->website,
                'tax_id' => $settings->tax_id,
                'description' => $settings->description,
                'logo_path' => $settings->logo_path,
                'logo_url' => $settings->logo_url,
                'updated_at' => $settings->updated_at,
            ],
        ]);
    }

    /**
     * Upload company logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = CompanySetting::current();

        // Delete old logo if exists
        if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
            Storage::disk('public')->delete($settings->logo_path);
        }

        // Store new logo
        $path = $request->file('logo')->store('company-logos', 'public');
        $settings->update(['logo_path' => $path]);

        return response()->json([
            'message' => 'Company logo uploaded successfully',
            'data' => [
                'logo_path' => $settings->logo_path,
                'logo_url' => $settings->logo_url,
            ],
        ]);
    }

    /**
     * Delete company logo
     */
    public function deleteLogo(): JsonResponse
    {
        $settings = CompanySetting::current();

        if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
            Storage::disk('public')->delete($settings->logo_path);
        }

        $settings->update(['logo_path' => null]);

        return response()->json([
            'message' => 'Company logo deleted successfully',
        ]);
    }
}
