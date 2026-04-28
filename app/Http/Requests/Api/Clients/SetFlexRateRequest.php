<?php

namespace App\Http\Requests\Api\Clients;

use Illuminate\Foundation\Http\FormRequest;

class SetFlexRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the gate
    }

    public function rules(): array
    {
        return [
            'flexrate_markup_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'per_service_markups' => ['nullable', 'array'],
        ];
    }
}
