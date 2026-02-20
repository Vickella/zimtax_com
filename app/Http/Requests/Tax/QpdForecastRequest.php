<?php

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

class QpdForecastRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tax_year' => ['required','integer','min:2000','max:2100'],
            'base_estimated_taxable_income' => ['required','numeric','min:0'],
            'growth_rate' => ['required','numeric','min:-1','max:10'], // allow negative or high growth
            'currency' => ['nullable','string','size:3'],
        ];
    }
}
