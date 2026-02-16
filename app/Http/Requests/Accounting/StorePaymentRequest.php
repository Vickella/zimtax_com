<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'payment_type' => ['required','in:RECEIPT,PAYMENT'],
            'party_type' => ['required','in:CUSTOMER,SUPPLIER,EMPLOYEE'],
            'party_id' => ['required','integer'],
            'bank_account_id' => ['required','integer'],

            'posting_date' => ['required','date'],
            'currency' => ['required','string','size:3'],
            'exchange_rate' => ['required','numeric','min:0.00000001'],
            'amount' => ['required','numeric','min:0.01'],
            'reference' => ['nullable','string','max:120'],

            // allocations optional, but if provided must be valid
            'allocations' => ['nullable','array'],
            'allocations.*.reference_type' => ['required_with:allocations','in:SALES_INVOICE,PURCHASE_INVOICE'],
            'allocations.*.reference_id' => ['required_with:allocations','integer'],
            'allocations.*.allocated_amount' => ['required_with:allocations','numeric','min:0.01'],
        ];
    }
}
