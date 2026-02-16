<?php

namespace App\Http\Requests\CompanySettings;

use Illuminate\Foundation\Http\FormRequest;

class StorePayeBracketRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'effective_from' => ['required','date'],
            'effective_to' => ['nullable','date','after_or_equal:effective_from'],
            'band_order' => ['required','integer','min:1'],
            'lower_bound' => ['required','numeric','min:0'],
            'upper_bound' => ['nullable','numeric','gt:0'],
            'rate' => ['required','numeric','min:0'],
            'base_tax' => ['required','numeric','min:0'],
        ];
    }
}
