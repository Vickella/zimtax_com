<?php

namespace App\Http\Controllers\CompanySettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySettings\StorePayrollStatutoryRequest;
use App\Models\PayrollStatutorySetting;
use App\Services\AuditLogger;

class PayrollStatutorySettingController extends Controller
{
    public function index()
    {
        $settings = PayrollStatutorySetting::query()
            ->where('company_id', company_id())
            ->orderByDesc('effective_from')
            ->limit(200)
            ->get();

        return view('modules.company-settings.payroll-statutory', compact('settings'));
    }

    public function store(StorePayrollStatutoryRequest $request)
    {
        $data = $request->validated();

        $necRate = $data['nec_rate'] ?? null;
        unset($data['nec_rate']);

        $data['company_id'] = company_id();
        $data['metadata'] = [
            'nec_rate' => $necRate, // NEC not in schema: stored in metadata
        ];

        $row = PayrollStatutorySetting::query()->create($data);
        AuditLogger::log('PayrollStatutorySetting', (int)$row->id, 'created', null, $row->toArray());

        return back()->with('ok', 'Statutory settings saved.');
    }
}
