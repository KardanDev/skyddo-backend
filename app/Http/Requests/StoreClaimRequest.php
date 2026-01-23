<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'policy_id' => ['required', 'exists:policies,id'],
            'description' => ['required', 'string'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'claim_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
