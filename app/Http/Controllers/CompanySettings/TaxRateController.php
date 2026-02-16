<?php

namespace App\Http\Controllers\CompanySettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySettings\StoreTaxRateRequest;
use App\Models\TaxRate;
use App\Services\AuditLogger;

class TaxRateController extends Controller
{
    public function index()
    {
        $rates = TaxRate::query()
            ->where('company_id', company_id())
            ->orderBy('tax_type')
            ->orderByDesc('effective_from')
            ->limit(300)
            ->get();

        return view('modules.company-settings.tax-rates', compact('rates'));
    }

    public function store(StoreTaxRateRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = company_id();
        $data['is_active'] = (int) $request->boolean('is_active', true);

        $rate = TaxRate::query()->create($data);
        AuditLogger::log('TaxRate', (int)$rate->id, 'created', null, $rate->toArray());

        return back()->with('ok', 'Tax rate saved.');
    }

    public function update(StoreTaxRateRequest $request, int $id)
    {
        $rate = TaxRate::query()->where('company_id', company_id())->where('id', $id)->firstOrFail();
        $old = $rate->toArray();

        $rate->fill($request->validated());
        $rate->is_active = (int) $request->boolean('is_active', true);
        $rate->save();

        AuditLogger::log('TaxRate', (int)$rate->id, 'updated', $old, $rate->toArray());

        return back()->with('ok', 'Tax rate updated.');
    }
}
