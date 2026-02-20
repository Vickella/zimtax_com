<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\Tax\IncomeTaxReturn;
use App\Models\Tax\TaxSetting;
use App\Services\Tax\IncomeTaxService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomeTaxController extends Controller
{
    public function __construct(private readonly IncomeTaxService $service) {}

    public function index()
    {
        $companyId = company_id();

        $returns = IncomeTaxReturn::where('company_id', $companyId)
            ->orderByDesc('period_end')
            ->paginate(20);

        return view('modules.tax.income.index', compact('returns'));
    }

    public function create()
    {
        return view('modules.tax.income.create');
    }

    public function store(Request $request)
    {
        $companyId = company_id();
        $settings = TaxSetting::forCompany($companyId);

        $data = $request->validate([
            'period_start' => ['required','date'],
            'period_end' => ['required','date','after_or_equal:period_start'],
            'override_taxable_income' => ['nullable','numeric'],
            'add_backs' => ['nullable','numeric','min:0'],
            'deductions' => ['nullable','numeric','min:0'],
            'notes' => ['nullable','string'],
        ]);

        $return = $this->service->buildAndSave($companyId, $settings, $data);

        return redirect()->route('modules.tax.income.show', $return->id)
            ->with('success', 'Income Tax Return created.');
    }

    public function show(IncomeTaxReturn $incomeTaxReturn)
    {
        abort_unless((int)$incomeTaxReturn->company_id === (int)company_id(), 403);

        return view('modules.tax.income.show', compact('incomeTaxReturn'));
    }

    public function pdf(IncomeTaxReturn $incomeTaxReturn)
    {
        abort_unless((int)$incomeTaxReturn->company_id === (int)company_id(), 403);

        $pdf = app('dompdf.wrapper')->loadView('modules.tax.print.itf12c', [
            'incomeTaxReturn' => $incomeTaxReturn
        ])->setPaper('a4');

        return $pdf->download("ITF12C_{$incomeTaxReturn->period_end->format('Ymd')}.pdf");
    }

    public function excel(IncomeTaxReturn $incomeTaxReturn): StreamedResponse
    {
        abort_unless((int)$incomeTaxReturn->company_id === (int)company_id(), 403);

        $filename = "ITF12C_{$incomeTaxReturn->period_end->format('Ymd')}.csv";

        return response()->streamDownload(function () use ($incomeTaxReturn) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Field', 'Value']);
            fputcsv($out, ['Period Start', $incomeTaxReturn->period_start?->format('Y-m-d')]);
            fputcsv($out, ['Period End', $incomeTaxReturn->period_end?->format('Y-m-d')]);
            fputcsv($out, ['Income Tax Rate', $incomeTaxReturn->income_tax_rate]);
            fputcsv($out, ['Profit Before Tax', $incomeTaxReturn->profit_before_tax]);
            fputcsv($out, ['Add backs', $incomeTaxReturn->add_backs]);
            fputcsv($out, ['Deductions', $incomeTaxReturn->deductions]);
            fputcsv($out, ['Taxable Income', $incomeTaxReturn->taxable_income]);
            fputcsv($out, ['Income Tax Payable', $incomeTaxReturn->income_tax_payable]);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
