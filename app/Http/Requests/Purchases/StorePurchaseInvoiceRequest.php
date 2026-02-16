<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // Normalize lines: remove fully empty rows
        $lines = collect($this->input('lines', []))
            ->filter(function ($l) {
                $item = $l['item_id'] ?? null;
                $qty  = $l['qty'] ?? null;
                $rate = $l['rate'] ?? null;
                return !blank($item) || !blank($qty) || !blank($rate);
            })
            ->values()
            ->all();

        $this->merge([
            'lines' => $lines,
            'currency' => strtoupper((string)$this->input('currency')),
        ]);
    }

    public function rules(): array
    {
        $companyId = company_id();

        return [
            'supplier_id' => [
                'required','integer',
                Rule::exists('suppliers', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'supplier_invoice_no' => ['nullable','string','max:80'],
            'input_tax_document_ref' => ['nullable','string','max:120'],
            'bill_of_entry_ref' => ['nullable','string','max:120'],
            'posting_date' => ['required','date'],
            'due_date' => ['nullable','date','after_or_equal:posting_date'],

            'currency' => [
                'required','string','size:3',
                Rule::exists('currencies', 'code')->where(fn ($q) => $q->where('is_active', 1)),
            ],
            'exchange_rate' => ['required','numeric','min:0.00000001'],
            'remarks' => ['nullable','string','max:255'],

            'lines' => ['required','array','min:1'],

            'lines.*.item_id' => [
                'required','integer',
                Rule::exists('items', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('is_active', 1)),
            ],
            'lines.*.warehouse_id' => [
                'nullable','integer',
                Rule::exists('warehouses', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('is_active', 1)),
            ],

            'lines.*.description' => ['nullable','string','max:255'],
            'lines.*.qty' => ['required','numeric','min:0.0001'],
            'lines.*.rate' => ['required','numeric','min:0'],

            // IMPORTANT: VAT is computed server-side.
            // We intentionally do NOT accept vat_rate or vat_amount from the client.
        ];
    }

    public function messages(): array
    {
        return [
            'lines.min' => 'Add at least one invoice line.',
            'lines.*.item_id.required' => 'Each line must have an item.',
            'lines.*.qty.required' => 'Each line must have a quantity.',
            'lines.*.rate.required' => 'Each line must have a rate.',
            'due_date.after_or_equal' => 'Due date cannot be before posting date.',
        ];
    }
}
