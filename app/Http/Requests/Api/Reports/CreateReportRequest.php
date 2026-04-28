<?php

namespace App\Http\Requests\Api\Reports;

use Illuminate\Foundation\Http\FormRequest;

class CreateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Action enforces the right
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:shipment,tracker,payment_log,refund'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
