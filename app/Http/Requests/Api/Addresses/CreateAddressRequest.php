<?php

namespace App\Http\Requests\Api\Addresses;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;

class CreateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Address::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'street1' => ['required', 'string', 'max:120'],
            'street2' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:40'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:24'],
            'email' => ['nullable', 'email', 'max:190'],
            'residential' => ['nullable', 'boolean'],
            'client_id' => ['nullable', 'integer'],
            'verify' => ['nullable', 'boolean'],
        ];
    }
}
