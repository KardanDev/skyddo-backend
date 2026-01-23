<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'exists:clients,id'],
            'insurer_id' => ['sometimes', 'exists:insurers,id'],
            'insurance_type' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sum_insured' => ['sometimes', 'numeric', 'min:0'],
            'premium' => ['sometimes', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'status' => ['sometimes', Rule::in(['active', 'expired', 'cancelled', 'pending_renewal'])],
        ];
    }
}
