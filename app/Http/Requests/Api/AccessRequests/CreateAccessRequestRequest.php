<?php

namespace App\Http\Requests\Api\AccessRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccessRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'requested_permission' => ['required', 'string', 'max:96'],
            'target_url' => ['nullable', 'string', 'max:512'],
        ];
    }
}
