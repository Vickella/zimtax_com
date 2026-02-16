<?php

namespace App\Http\Requests\CompanySettings;

use Illuminate\Foundation\Http\FormRequest;

class StoreFiscalPeriodRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:50'],
            'period_type' => ['required','in:MONTH,QUARTER,YEAR'],
            'start_date' => ['required','date'],
            'end_date' => ['required','date','after_or_equal:start_date'],
        ];
    }
}
