<?php

namespace App\Http\Requests\Api\Shipments;

use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;

class CreateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Shipment::class) ?? false;
    }

    public function rules(): array
    {
        $addressRules = [
            'street1' => ['required', 'string', 'max:120'],
            'street2' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:40'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'name' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:24'],
            'email' => ['nullable', 'email', 'max:190'],
        ];

        return [
            'to_address' => ['required'],
            'to_address.street1' => ['required_array_keys:street1', 'string', 'max:120'],
            'from_address' => ['required'],
            'parcel' => ['required', 'array'],
            'parcel.weight_oz' => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'parcel.length_in' => ['nullable', 'numeric', 'min:0.1'],
            'parcel.width_in' => ['nullable', 'numeric', 'min:0.1'],
            'parcel.height_in' => ['nullable', 'numeric', 'min:0.1'],
            'parcel.predefined_package' => ['nullable', 'string', 'max:48'],
            'reference' => ['nullable', 'string', 'max:64'],
            'is_return' => ['nullable', 'boolean'],
            'client_id' => ['nullable', 'integer'],
            'options' => ['nullable', 'array'],
            'declared_value_cents' => ['nullable', 'integer', 'min:0'],

            // Allow either an int ID or a nested address array per key:
            ...collect($addressRules)
                ->flatMap(fn ($r, $k) => [
                    "to_address.{$k}" => ['sometimes', ...$r],
                    "from_address.{$k}" => ['sometimes', ...$r],
                ])->all(),
        ];
    }
}
