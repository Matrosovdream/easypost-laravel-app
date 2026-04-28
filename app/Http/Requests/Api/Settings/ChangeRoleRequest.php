<?php

namespace App\Http\Requests\Api\Settings;

use Illuminate\Foundation\Http\FormRequest;

class ChangeRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the rights
    }

    public function rules(): array
    {
        return [
            'role_slug' => ['required', 'in:admin,manager,shipper,cs_agent,client,viewer'],
        ];
    }
}
