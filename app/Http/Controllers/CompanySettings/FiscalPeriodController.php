<?php

namespace App\Http\Controllers\CompanySettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySettings\StoreFiscalPeriodRequest;
use App\Models\FiscalPeriod;
use App\Services\AuditLogger;

class FiscalPeriodController extends Controller
{
    public function index()
    {
        $periods = FiscalPeriod::query()
            ->where('company_id', company_id())
            ->orderByDesc('start_date')
            ->limit(200)
            ->get();

        return view('modules.company-settings.fiscal-periods', compact('periods'));
    }

    public function store(StoreFiscalPeriodRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = company_id();
        $data['is_closed'] = 0;

        $period = FiscalPeriod::query()->create($data);
        AuditLogger::log('FiscalPeriod', (int)$period->id, 'created', null, $period->toArray());

        return back()->with('ok', 'Fiscal period created.');
    }

    public function close(int $id)
    {
        $period = FiscalPeriod::query()->where('company_id', company_id())->where('id', $id)->firstOrFail();

        if ($period->is_closed) return back()->with('ok', 'Already closed.');

        $old = $period->toArray();

        $period->is_closed = 1;
        $period->closed_at = now();
        $period->closed_by = auth()->id();
        $period->save();

        AuditLogger::log('FiscalPeriod', (int)$period->id, 'closed', $old, $period->toArray());

        return back()->with('ok', 'Period closed.');
    }
}
