<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        // You can replace with policy later
        return true;
    }

    public function rules(): array
    {
        return [
            'posting_date' => ['required', 'date'],
            'customer_id'  => ['required', 'integer'],
            'bank_account_id' => ['required', 'integer'],
            'reference'    => ['nullable', 'string', 'max:100'],
            'memo'         => ['nullable', 'string', 'max:255'],

            // if you submit allocations
            'allocations'              => ['nullable', 'array'],
            'allocations.*.invoice_id' => ['required_with:allocations', 'integer'],
            'allocations.*.amount'     => ['required_with:allocations', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required.',
            'bank_account_id.required' => 'Bank account is required.',
            'posting_date.required' => 'Posting date is required.',
        ];
    }
}
