<?php

namespace App\Http\Requests\Rest\PublicApi;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'topic' => ['nullable', 'in:sales,demo,partnerships,support,other'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }
}
