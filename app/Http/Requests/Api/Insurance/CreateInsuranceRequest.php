<?php

namespace App\Http\Requests\Api\Insurance;

use App\Models\Insurance;
use Illuminate\Foundation\Http\FormRequest;

class CreateInsuranceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Insurance::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tracking_code' => ['required', 'string', 'max:64'],
            'carrier' => ['required', 'string', 'max:48'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'shipment_id' => ['nullable', 'integer'],
            'reference' => ['nullable', 'string', 'max:64'],
            'to_address' => ['nullable', 'array'],
            'from_address' => ['nullable', 'array'],
        ];
    }
}
