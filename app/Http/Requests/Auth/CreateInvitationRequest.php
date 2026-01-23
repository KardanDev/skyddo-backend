<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email', 'unique:invitations,email,NULL,id,accepted_at,NULL'],
            'role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_MEMBER])],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered or has a pending invitation.',
            'role.in' => 'Invalid role. You can only invite admins or members.',
        ];
    }
}
