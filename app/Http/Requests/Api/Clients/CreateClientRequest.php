<?php

namespace App\Http\Requests\Api\Clients;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class CreateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Client::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:120'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:24'],
            'flexrate_markup_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'billing_mode' => ['nullable', 'in:postpaid,prepaid'],
            'credit_terms_days' => ['nullable', 'integer', 'min:0', 'max:180'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
