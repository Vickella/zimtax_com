<?php

namespace App\Http\Controllers\CompanySettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySettings\StoreExchangeRateRequest;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Services\AuditLogger;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $rates = ExchangeRate::query()
            ->where('company_id', company_id())
            ->orderByDesc('rate_date')
            ->orderBy('base_currency')
            ->orderBy('quote_currency')
            ->limit(200)
            ->get();

        $currencies = Currency::query()->where('is_active', 1)->orderBy('code')->get();

        return view('modules.company-settings.exchange-rates', compact('rates', 'currencies'));
    }

    public function store(StoreExchangeRateRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = company_id();

        $rate = ExchangeRate::query()->create($data);
        AuditLogger::log('ExchangeRate', (int)$rate->id, 'created', null, $rate->toArray());

        return back()->with('ok', 'Exchange rate saved.');
    }

    public function destroy(int $id)
    {
        $rate = ExchangeRate::query()->where('company_id', company_id())->where('id', $id)->firstOrFail();
        $old = $rate->toArray();
        $rate->delete();

        AuditLogger::log('ExchangeRate', (int)$id, 'deleted', $old, null);

        return back()->with('ok', 'Exchange rate deleted.');
    }
}
