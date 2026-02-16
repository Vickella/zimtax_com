<?php

namespace App\Http\Requests\CompanySettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required','string','max:50'],
            'name' => ['required','string','max:255'],
            'trading_name' => ['nullable','string','max:255'],
            'tin' => ['nullable','string','max:50'],
            'vat_number' => ['nullable','string','max:50'],
            'address' => ['nullable','string'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:255'],
            'base_currency' => ['required','string','size:3'],
            'fiscal_year_start_month' => ['required','integer','min:1','max:12'],
            'is_active' => ['nullable','boolean'],
        ];
    }
}
