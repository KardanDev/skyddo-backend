<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'string'],
            'incident_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'claim_amount' => ['nullable', 'numeric', 'min:0'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['submitted', 'under_review', 'docs_requested', 'forwarded', 'approved', 'rejected', 'settled'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
