<?php

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

class VatReturnRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'period_start' => ['required','date'],
            'period_end' => ['required','date','after_or_equal:period_start'],
            'adjustments' => ['nullable','numeric'],
            'notes' => ['nullable','string','max:255'],
        ];
    }
}
