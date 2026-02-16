<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required','string','max:50'],
            'name' => ['required','string','max:255'],
            'tin' => ['nullable','string','max:50'],
            'vat_number' => ['nullable','string','max:50'],
            'bank_details' => ['nullable','string'],
            'withholding_tax_flag' => ['nullable','boolean'],
            'is_active' => ['nullable','boolean'],
        ];
    }
}
