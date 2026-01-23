<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documentable_type' => ['required', Rule::in(['quote', 'policy', 'claim'])],
            'documentable_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:10240'], // 10MB max
        ];
    }
}
