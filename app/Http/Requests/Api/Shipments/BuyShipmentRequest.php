<?php

namespace App\Http\Requests\Api\Shipments;

use Illuminate\Foundation\Http\FormRequest;

class BuyShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rate_id' => ['required', 'string', 'max:64'],
            'insurance_cents' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}
