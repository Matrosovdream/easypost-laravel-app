<?php

namespace App\Http\Requests\Api\Pickups;

use Illuminate\Foundation\Http\FormRequest;

class BuyPickupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the gate
    }

    public function rules(): array
    {
        return [
            'carrier' => ['required', 'string', 'max:48'],
            'service' => ['required', 'string', 'max:48'],
        ];
    }
}
