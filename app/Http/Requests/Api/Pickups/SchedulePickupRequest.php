<?php

namespace App\Http\Requests\Api\Pickups;

use App\Models\Pickup;
use Illuminate\Foundation\Http\FormRequest;

class SchedulePickupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Pickup::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer'],
            'warehouse_id' => ['nullable', 'integer'],
            'min_datetime' => ['required', 'date'],
            'max_datetime' => ['required', 'date', 'after:min_datetime'],
            'instructions' => ['nullable', 'string', 'max:500'],
            'reference' => ['nullable', 'string', 'max:64'],
            'is_account_address' => ['nullable', 'boolean'],
        ];
    }
}
