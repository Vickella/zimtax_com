<?php

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

class IncomeTaxReturnRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tax_year' => ['required','integer','min:2000','max:2100'],
            'period_start' => ['required','date'],
            'period_end' => ['required','date','after_or_equal:period_start'],

            'non_deductible_expenses' => ['nullable','numeric'],
            'capital_allowances' => ['nullable','numeric'],
            'assessed_losses_bf' => ['nullable','numeric'],
            'other_adjustments' => ['nullable','numeric'],
            'notes' => ['nullable','string','max:255'],
        ];
    }
}
