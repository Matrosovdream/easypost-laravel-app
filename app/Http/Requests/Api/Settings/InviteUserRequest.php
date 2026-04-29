<?php

namespace App\Http\Requests\Api\Settings;

use Illuminate\Foundation\Http\FormRequest;

class InviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the rights
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:190'],
            'role_slug' => ['required', 'in:admin,manager,shipper,cs_agent,client,viewer'],
            'client_id' => ['nullable', 'integer'],
            'spending_cap_cents' => ['nullable', 'integer', 'min:0'],
            'daily_cap_cents' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
