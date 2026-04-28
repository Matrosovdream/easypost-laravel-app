<?php

namespace App\Http\Requests\Api\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prefs' => ['required', 'array'],
        ];
    }
}
