<?php

namespace App\Http\Requests\CompanySettings;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'base_currency' => ['required','string','size:3'],
            'quote_currency' => ['required','string','size:3','different:base_currency'],
            'rate' => ['required','numeric','gt:0'],
            'rate_date' => ['required','date'],
            'source' => ['nullable','string','max:100'],
        ];
    }
}
