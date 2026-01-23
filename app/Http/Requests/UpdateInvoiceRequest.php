<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'paid_amount' => ['sometimes', 'numeric', 'min:0'],
            'due_date' => ['sometimes', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::in(['draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
