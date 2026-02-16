<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required','string','max:50'],
            'name' => ['required','string','max:255'],
            'tin' => ['nullable','string','max:50'],
            'vat_number' => ['nullable','string','max:50'],
            'address' => ['nullable','string'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:255'],
            'credit_limit' => ['nullable','numeric','min:0'],
            'currency' => ['nullable','string','size:3'],
            'is_active' => ['nullable','boolean'],
        ];
    }
}
