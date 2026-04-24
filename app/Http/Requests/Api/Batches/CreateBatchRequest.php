<?php

namespace App\Http\Requests\Api\Batches;

use App\Models\Batch;
use Illuminate\Foundation\Http\FormRequest;

class CreateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Batch::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'shipment_ids' => ['required', 'array', 'min:1', 'max:500'],
            'shipment_ids.*' => ['integer'],
            'reference' => ['nullable', 'string', 'max:64'],
        ];
    }
}
