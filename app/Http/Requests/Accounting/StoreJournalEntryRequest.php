<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'posting_date' => ['required','date'],
            'memo' => ['nullable','string','max:255'],
            'currency' => ['required','string','size:3'],
            'exchange_rate' => ['required','numeric','min:0.00000001'],

            'lines' => ['required','array','min:2'],
            'lines.*.account_id' => ['required','integer'],
            'lines.*.description' => ['nullable','string','max:255'],
            'lines.*.debit' => ['nullable','numeric','min:0'],
            'lines.*.credit' => ['nullable','numeric','min:0'],
            'lines.*.party_type' => ['nullable','in:CUSTOMER,SUPPLIER,EMPLOYEE,NONE'],
            'lines.*.party_id' => ['nullable','integer'],
            'lines.*.cost_center' => ['nullable','string','max:80'],
        ];
    }
}
