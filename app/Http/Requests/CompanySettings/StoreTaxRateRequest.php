<?php

namespace App\Http\Requests\CompanySettings;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tax_type' => ['required','in:VAT,WHT,AIDS_LEVY,OTHER'],
            'code' => ['required','string','max:50'],
            'description' => ['nullable','string','max:255'],
            'rate' => ['required','numeric','min:0'],
            'effective_from' => ['required','date'],
            'effective_to' => ['nullable','date','after_or_equal:effective_from'],
            'is_active' => ['nullable','boolean'],
        ];
    }
}
