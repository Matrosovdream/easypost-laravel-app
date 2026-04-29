<?php

namespace App\Http\Requests\Api\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the rights
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'time_zone' => ['sometimes', 'string', 'max:64'],
            'default_currency' => ['sometimes', 'string', 'size:3'],
            'settings' => ['sometimes', 'array'],
            'logo_s3_key' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
