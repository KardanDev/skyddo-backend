<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'insurer_id' => ['required', 'exists:insurers,id'],
            'quote_id' => ['nullable', 'exists:quotes,id'],
            'insurance_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sum_insured' => ['required', 'numeric', 'min:0'],
            'premium' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }
}
