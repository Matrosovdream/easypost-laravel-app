<?php

namespace App\Http\Requests\Api\Trackers;

use App\Models\Tracker;
use Illuminate\Foundation\Http\FormRequest;

class CreateTrackerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Tracker::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tracking_code' => ['required', 'string', 'max:64'],
            'carrier' => ['required', 'string', 'max:48'],
        ];
    }
}
