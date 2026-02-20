<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRunRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'period_id' => ['required','integer'], // fiscal_periods.id (MONTH)
            'currency' => ['required','string','size:3'],
            'exchange_rate' => ['required','numeric','min:0.00000001'],
        ];
    }
}
