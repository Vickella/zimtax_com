<?php

namespace App\Http\Requests\CompanySettings;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return true; 
    }

    public function rules(): array
    {
        $code = $this->route('code');
        
        $rules = [
            'code' => ['required', 'string', 'size:3'],
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ];
        
        // When updating, don't require code if it's in the URL
        if ($code) {
            unset($rules['code']);
        }
        
        return $rules;
    }

    protected function prepareForValidation()
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code)
            ]);
        }
        
        // Ensure is_active is set
        if (!$this->has('is_active')) {
            $this->merge([
                'is_active' => false
            ]);
        }
    }
}