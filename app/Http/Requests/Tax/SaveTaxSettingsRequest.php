<?php

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

class SaveTaxSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vat_rate' => ['required','numeric','min:0','max:1'],
            'income_tax_rate' => ['required','numeric','min:0','max:1'],

            'vat_output_account_id' => ['nullable','integer'],
            'vat_input_account_id' => ['nullable','integer'],

            'income_tax_expense_account_id' => ['nullable','integer'],
            'income_tax_payable_account_id' => ['nullable','integer'],
            'provisional_tax_payable_account_id' => ['nullable','integer'],

            'taxpayer_name' => ['nullable','string','max:200'],
            'trade_name' => ['nullable','string','max:200'],
            'bp_number' => ['nullable','string','max:80'],
            'tin' => ['nullable','string','max:80'],
            'vat_number' => ['nullable','string','max:80'],
            'address' => ['nullable','string','max:255'],
            'email' => ['nullable','string','max:120'],
            'phone' => ['nullable','string','max:80'],
            'nature_of_business' => ['nullable','string','max:200'],
        ];
    }
}
