<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_no' => ['required','string','max:50'],
            'first_name' => ['required','string','max:120'],
            'last_name' => ['required','string','max:120'],
            'national_id' => ['nullable','string','max:50'],
            'tin' => ['nullable','string','max:50'],
            'nssa_number' => ['nullable','string','max:50'],
            'nec' => ['nullable','string','max:50'],
            'bank_name' => ['nullable','string','max:120'],
            'bank_account_number' => ['nullable','string','max:80'],
            'currency' => ['required','string','size:3'],
            'hire_date' => ['nullable','date'],
            'status' => ['required','in:ACTIVE,INACTIVE'],

            // salary structure
            'earnings' => ['nullable','array'],
            'earnings.*.component_id' => ['nullable','integer'],
            'earnings.*.amount' => ['nullable','numeric','min:0'],

            'deductions' => ['nullable','array'],
            'deductions.*.component_id' => ['nullable','integer'],
            'deductions.*.amount' => ['nullable','numeric','min:0'],
        ];
    }
}
