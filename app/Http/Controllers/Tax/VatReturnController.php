<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\Tax\TaxSetting;
use App\Models\Tax\VatReturn;
use App\Services\Tax\VatReturnService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VatReturnController extends Controller
{
    public function __construct(private readonly VatReturnService $service) {}

    public function index()
    {
        $companyId = company_id();

        $returns = VatReturn::where('company_id', $companyId)
            ->orderByDesc('period_end')
            ->paginate(20);

        return view('modules.tax.vat.index', compact('returns'));
    }

    public function create()
    {
        return view('modules.tax.vat.create');
    }

    public function store(Request $request)
    {
        $companyId = company_id();
        $settings = TaxSetting::forCompany($companyId);

        $data = $request->validate([
            'period_start' => ['required','date'],
            'period_end' => ['required','date','after_or_equal:period_start'],

            // optional overrides
            'override_taxable_sales' => ['nullable','numeric','min:0'],
            'override_taxable_purchases' => ['nullable','numeric','min:0'],
            'override_output_vat' => ['nullable','numeric','min:0'],
            'override_input_vat' => ['nullable','numeric','min:0'],
            'notes' => ['nullable','string'],
        ]);

        $vatReturn = $this->service->buildAndSave($companyId, $settings, $data);

        return redirect()->route('modules.tax.vat.show', $vatReturn->id)
            ->with('success', 'VAT Return created.');
    }

    public function show(VatReturn $vatReturn)
    {
        abort_unless((int)$vatReturn->company_id === (int)company_id(), 403);

        return view('modules.tax.vat.show', compact('vatReturn'));
    }

    public function pdf(VatReturn $vatReturn)
    {
        abort_unless((int)$vatReturn->company_id === (int)company_id(), 403);

        // DomPDF (barryvdh/laravel-dompdf)
        $pdf = app('dompdf.wrapper')->loadView('modules.tax.print.vat7', [
            'vatReturn' => $vatReturn
        ])->setPaper('a4');

        return $pdf->download("VAT7_{$vatReturn->period_end->format('Ymd')}.pdf");
    }

    public function excel(VatReturn $vatReturn): StreamedResponse
    {
        abort_unless((int)$vatReturn->company_id === (int)company_id(), 403);

        // CSV that opens in Excel
        $filename = "VAT7_{$vatReturn->period_end->format('Ymd')}.csv";

        return response()->streamDownload(function () use ($vatReturn) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Field', 'Value']);
            fputcsv($out, ['Period Start', $vatReturn->period_start?->format('Y-m-d')]);
            fputcsv($out, ['Period End', $vatReturn->period_end?->format('Y-m-d')]);
            fputcsv($out, ['VAT Rate', $vatReturn->vat_rate]);
            fputcsv($out, ['Taxable Sales', $vatReturn->taxable_sales]);
            fputcsv($out, ['Output VAT', $vatReturn->output_vat]);
            fputcsv($out, ['Taxable Purchases', $vatReturn->taxable_purchases]);
            fputcsv($out, ['Input VAT', $vatReturn->input_vat]);
            fputcsv($out, ['Net VAT Payable', $vatReturn->net_vat_payable]);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
