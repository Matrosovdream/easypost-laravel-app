<?php

namespace App\Http\Requests\Api\Returns;

use App\Models\ReturnRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ReturnRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'original_shipment_id' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:64'],
            'items' => ['nullable', 'array'],
            'items.*' => ['string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'auto_refund' => ['nullable', 'boolean'],
        ];
    }
}
