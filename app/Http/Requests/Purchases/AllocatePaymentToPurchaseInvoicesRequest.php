<?php


namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;

class AllocatePaymentToPurchaseInvoicesRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'payment_id' => ['required','integer'],
            'allocations' => ['required','array','min:1'],
            'allocations.*.purchase_invoice_id' => ['required','integer'],
            'allocations.*.amount' => ['required','numeric','min:0.01'],
        ];
    }
}
