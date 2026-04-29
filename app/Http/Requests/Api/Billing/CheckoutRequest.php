<?php

namespace App\Http\Requests\Api\Billing;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the rights
    }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'string'],
        ];
    }
}
