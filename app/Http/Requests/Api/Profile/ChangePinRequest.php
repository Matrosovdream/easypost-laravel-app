<?php

namespace App\Http\Requests\Api\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ChangePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => ['required', 'string', 'between:4,8'],
            'new_pin' => ['required', 'string', 'between:4,8', 'different:current_pin'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
        ];
    }
}
