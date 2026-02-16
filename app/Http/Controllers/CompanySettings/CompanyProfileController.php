<?php

namespace App\Http\Controllers\CompanySettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySettings\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Currency;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    public function edit(Request $request)
    {
        /** @var Company $company */
        $company = $request->attributes->get('company');

        $currencies = Currency::query()
            ->where('is_active', 1)
            ->orderBy('code')
            ->get();

        return view('modules.company-settings.company', compact('company', 'currencies'));
    }

    public function update(UpdateCompanyRequest $request)
    {
        /** @var Company $company */
        $company = $request->attributes->get('company');

        $old = $company->toArray();

        $company->fill($request->validated());
        $company->is_active = (int) ($request->boolean('is_active', true));
        $company->save();

        AuditLogger::log('Company', (int) $company->id, 'updated', $old, $company->toArray());

        return back()->with('ok', 'Company updated.');
    }
}
