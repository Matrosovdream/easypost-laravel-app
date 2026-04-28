<?php

namespace App\Http\Requests\Api\ScanForms;

use Illuminate\Foundation\Http\FormRequest;

class CreateScanFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the gate
    }

    public function rules(): array
    {
        return [
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['integer'],
        ];
    }
}
