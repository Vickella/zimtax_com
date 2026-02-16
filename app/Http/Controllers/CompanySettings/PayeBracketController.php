<?php

namespace App\Http\Controllers\CompanySettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySettings\StorePayeBracketRequest;
use App\Models\PayeBracket;
use App\Services\AuditLogger;

class PayeBracketController extends Controller
{
    public function index()
    {
        $rows = PayeBracket::query()
            ->where('company_id', company_id())
            ->orderByDesc('effective_from')
            ->orderBy('band_order')
            ->limit(400)
            ->get();

        return view('modules.company-settings.paye-brackets', compact('rows'));
    }

    public function store(StorePayeBracketRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = company_id();

        $row = PayeBracket::query()->create($data);
        AuditLogger::log('PayeBracket', (int)$row->id, 'created', null, $row->toArray());

        return back()->with('ok', 'PAYE bracket saved.');
    }

    public function destroy(int $id)
    {
        $row = PayeBracket::query()->where('company_id', company_id())->where('id', $id)->firstOrFail();
        $old = $row->toArray();
        $row->delete();

        AuditLogger::log('PayeBracket', (int)$id, 'deleted', $old, null);

        return back()->with('ok', 'PAYE bracket deleted.');
    }
}
