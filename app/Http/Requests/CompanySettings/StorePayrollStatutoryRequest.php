<?php

namespace App\Http\Requests\CompanySettings;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollStatutoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'effective_from' => ['required','date'],
            'effective_to' => ['nullable','date','after_or_equal:effective_from'],
            'nssa_employee_rate' => ['required','numeric','min:0'],
            'nssa_employer_rate' => ['required','numeric','min:0'],
            'nssa_ceiling_amount' => ['required','numeric','min:0'],
            'aids_levy_rate' => ['required','numeric','min:0'],
            'zimdef_employee_rate' => ['nullable','numeric','min:0'],
            'zimdef_employer_rate' => ['nullable','numeric','min:0'],
            'nec_rate' => ['nullable','numeric','min:0'], // stored into metadata
        ];
    }
}
