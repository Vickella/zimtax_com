<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\Tax\Itf12bPayment;
use App\Models\Tax\Itf12bProjection;
use App\Models\Tax\TaxSetting;
use App\Services\Tax\QpdService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QpdController extends Controller
{
    public function __construct(private readonly QpdService $service) {}

    public function index()
    {
        $companyId = company_id();

        $projections = Itf12bProjection::where('company_id', $companyId)
            ->orderByDesc('tax_year')
            ->paginate(20);

        return view('modules.tax.qpd.index', compact('projections'));
    }

    public function create()
    {
        return view('modules.tax.qpd.create');
    }

    public function store(Request $request)
    {
        $companyId = company_id();
        $settings = TaxSetting::forCompany($companyId);

        $data = $request->validate([
            'tax_year' => ['required','integer','min:2000','max:2100'],
            'base_taxable_income' => ['required','numeric','min:0'],
            'growth_rate' => ['nullable','numeric','min:-1','max:10'], // e.g 0.10
            'notes' => ['nullable','string'],
        ]);

        $projection = $this->service->createProjection($companyId, $settings, $data);

        return redirect()->route('modules.tax.qpd.show', $projection->id)
            ->with('success', 'QPD projection created.');
    }

    public function show(Itf12bProjection $projection)
    {
        abort_unless((int)$projection->company_id === (int)company_id(), 403);

        $settings = TaxSetting::forCompany(company_id());

        $summary = $this->service->summary($projection, $settings);

        return view('modules.tax.qpd.show', compact('projection','summary','settings'));
    }

    public function recordPayment(Request $request, Itf12bProjection $projection, int $quarterNo)
    {
        abort_unless((int)$projection->company_id === (int)company_id(), 403);
        abort_unless(in_array($quarterNo, [1,2,3,4], true), 404);

        $data = $request->validate([
            'payment_date' => ['required','date'],
            'amount' => ['required','numeric','min:0'],
            'reference' => ['nullable','string','max:120'],
        ]);

        Itf12bPayment::create([
            'company_id' => company_id(),
            'itf12b_projection_id' => $projection->id,
            'quarter_no' => $quarterNo,
            'payment_date' => $data['payment_date'],
            'amount' => $data['amount'],
            'reference' => $data['reference'] ?? null,
        ]);

        return back()->with('success', "Q{$quarterNo} payment recorded.");
    }

    public function pdf(Itf12bProjection $projection, int $quarterNo)
    {
        abort_unless((int)$projection->company_id === (int)company_id(), 403);

        $settings = TaxSetting::forCompany(company_id());
        $summary = $this->service->summary($projection, $settings);
        $q = $summary['quarters'][$quarterNo] ?? null;
        abort_unless($q !== null, 404);

        $pdf = app('dompdf.wrapper')->loadView('modules.tax.print.itf12b', [
            'projection' => $projection,
            'settings' => $settings,
            'summary' => $summary,
            'quarterNo' => $quarterNo,
            'quarter' => $q,
        ])->setPaper('a4');

        return $pdf->download("ITF12B_{$projection->tax_year}_Q{$quarterNo}.pdf");
    }

    public function excel(Itf12bProjection $projection): StreamedResponse
    {
        abort_unless((int)$projection->company_id === (int)company_id(), 403);

        $settings = TaxSetting::forCompany(company_id());
        $summary = $this->service->summary($projection, $settings);

        $filename = "ITF12B_{$projection->tax_year}.csv";

        return response()->streamDownload(function () use ($projection, $summary) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tax Year', $projection->tax_year]);
            fputcsv($out, ['Estimated Taxable Income', $summary['estimated_taxable_income']]);
            fputcsv($out, ['Estimated Tax Payable', $summary['estimated_tax_payable']]);
            fputcsv($out, []);
            fputcsv($out, ['Quarter','Cumulative %','Cumulative Due','Paid To Date','Balance Due This Quarter','Due Date']);

            foreach ($summary['quarters'] as $qNo => $q) {
                fputcsv($out, [
                    "Q{$qNo}",
                    $q['cumulative_percent'],
                    $q['cumulative_due'],
                    $q['paid_to_date'],
                    $q['balance_due'],
                    $q['due_date'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
