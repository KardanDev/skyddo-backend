<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'exists:clients,id'],
            'insurer_id' => ['nullable', 'exists:insurers,id'],
            'insurance_type' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sum_insured' => ['nullable', 'numeric', 'min:0'],
            'premium' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['pending', 'sent_to_insurer', 'received', 'approved', 'rejected', 'expired'])],
            'valid_until' => ['nullable', 'date'],
            'comparison_data' => ['nullable', 'array'],
        ];
    }
}
