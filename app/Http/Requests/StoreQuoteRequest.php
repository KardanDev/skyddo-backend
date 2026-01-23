<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Client identification (either client_id OR client details required)
            'client_id' => ['nullable', 'exists:clients,id'],
            'client_name' => ['required_without:client_id', 'string', 'max:255'],
            'client_email' => ['required_without:client_id', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],

            // Insurance details
            'insurer_id' => ['nullable', 'exists:insurers,id'],
            'insurance_type_id' => ['required', 'exists:insurance_types,id'],
            'vehicle_type_id' => ['nullable', 'exists:vehicle_types,id'],
            'asset_value' => ['required', 'numeric', 'min:0'],

            // Additional details for requirements
            'additional_details' => ['nullable', 'array'],

            // Calculated/legacy fields (optional, auto-calculated if not provided)
            'calculated_cost' => ['nullable', 'numeric', 'min:0'],
            'insurance_type' => ['nullable', 'string', 'max:255'], // Legacy field
            'description' => ['nullable', 'string'],
            'sum_insured' => ['nullable', 'numeric', 'min:0'], // Legacy field
            'premium' => ['nullable', 'numeric', 'min:0'], // Legacy field
            'status' => ['nullable', 'in:pending,sent_to_insurer,received,approved,rejected,expired'],
            'valid_until' => ['nullable', 'date', 'after:today'],
        ];
    }
}
