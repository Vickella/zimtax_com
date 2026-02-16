<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
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
            'customer_id' => [
                'required','integer',
                Rule::exists('customers', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('is_active', 1)),
            ],
            'posting_date' => ['required','date'],
            'due_date' => ['nullable','date','after_or_equal:posting_date'],

            'currency' => [
                'required','string','size:3',
                Rule::exists('currencies', 'code')->where(fn ($q) => $q->where('is_active', 1)),
            ],
            'exchange_rate' => ['nullable','numeric','min:0.00000001'],
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
            'lines.*.qty' => ['required','numeric','min:0.0001'],
            'lines.*.rate' => ['required','numeric','min:0'],
            'lines.*.description' => ['nullable','string','max:255'],

            // IMPORTANT: VAT is computed server-side.
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
