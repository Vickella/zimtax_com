<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\Tax\TaxSetting;
use Illuminate\Http\Request;

class TaxSettingsController extends Controller
{
    public function edit()
    {
        $companyId = company_id();
        $settings = TaxSetting::forCompany($companyId);

        return view('modules.tax.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $companyId = company_id();

        $data = $request->validate([
            'vat_rate' => ['required','numeric','min:0','max:1'],
            'income_tax_rate' => ['required','numeric','min:0','max:1'],

            'vat_output_account_code' => ['nullable','string','max:50'],
            'vat_input_account_code' => ['nullable','string','max:50'],

            'sales_income_account_codes' => ['nullable','string'],   // comma separated
            'purchases_expense_account_codes' => ['nullable','string'], // comma separated

            // QPD percentages + due dates
            'qpd_q1_percent' => ['required','numeric','min:0','max:1'],
            'qpd_q2_percent' => ['required','numeric','min:0','max:1'],
            'qpd_q3_percent' => ['required','numeric','min:0','max:1'],
            'qpd_q4_percent' => ['required','numeric','min:0','max:1'],

            'qpd_q1_due' => ['required','date_format:Y-m-d'],
            'qpd_q2_due' => ['required','date_format:Y-m-d'],
            'qpd_q3_due' => ['required','date_format:Y-m-d'],
            'qpd_q4_due' => ['required','date_format:Y-m-d'],
        ]);

        TaxSetting::upsertCompany($companyId, $data);

        return back()->with('success', 'Tax settings updated.');
    }
}
