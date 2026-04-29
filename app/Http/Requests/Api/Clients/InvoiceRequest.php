<?php

namespace App\Http\Requests\Api\Clients;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the gate
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ];
    }
}
