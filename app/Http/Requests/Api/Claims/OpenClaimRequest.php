<?php

namespace App\Http\Requests\Api\Claims;

use App\Models\Claim;
use Illuminate\Foundation\Http\FormRequest;

class OpenClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Claim::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'shipment_id' => ['required', 'integer'],
            'type' => ['required', 'in:damage,loss,missing_items'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'min:5', 'max:2000'],
            'insurance_id' => ['nullable', 'integer'],
            'assigned_to' => ['nullable', 'integer'],
        ];
    }
}
