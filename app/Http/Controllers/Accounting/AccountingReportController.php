<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Services\Accounting\AccountingReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingReportController extends Controller
{
    public function __construct(private AccountingReportService $service) {}

    public function trialBalance(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $rows = $this->service->trialBalance(company_id(), $from, $to);

        return view('modules.accounting.reports.trial_balance', compact('rows','from','to'));
    }

    public function profitLoss(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $report = $this->service->profitLoss(company_id(), $from, $to);

        return view('modules.accounting.reports.profit_loss', [
            'rows' => $report['rows'],
            'from' => $from,
            'to' => $to,
            'total_income' => $report['total_income'],
            'total_expenses' => $report['total_expenses'],
            'net_profit' => $report['net_profit'],
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->get('as_of', now()->toDateString());
        $report = $this->service->balanceSheet(company_id(), $asOf);

        return view('modules.accounting.reports.balance_sheet', array_merge($report, ['asOf' => $asOf]));
    }

    public function generalLedger(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $accountId = $request->get('account_id');

        $accounts = ChartOfAccount::query()->forCompany(company_id())->where('is_active',1)->orderBy('code')->get(['id','code','name']);
        $rows = $this->service->generalLedger(company_id(), $accountId ? (int)$accountId : null, $from, $to);

        return view('modules.accounting.reports.general_ledger', compact('rows','from','to','accounts','accountId'));
    }

    // --- CSV exports (downloadable, reliable in any hosting) ---

    public function trialBalanceCsv(Request $request): StreamedResponse
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $rows = $this->service->trialBalance(company_id(), $from, $to);

        return $this->csv("trial_balance_{$from}_{$to}.csv", ['Code','Name','Type','Debit','Credit','Net'], $rows, function($r){
            return [$r['code'],$r['name'],$r['account_type'],$r['debit'],$r['credit'],$r['net']];
        });
    }

    public function profitLossCsv(Request $request): StreamedResponse
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $rep = $this->service->profitLoss(company_id(), $from, $to);

        $rows = $rep['rows'];

        return $this->csv("profit_loss_{$from}_{$to}.csv", ['Code','Name','Type','Debit','Credit','Balance'], $rows, function($r){
            return [$r['code'],$r['name'],$r['account_type'],$r['debit'],$r['credit'],$r['balance']];
        });
    }

    public function balanceSheetCsv(Request $request): StreamedResponse
    {
        $asOf = $request->get('as_of', now()->toDateString());
        $rep = $this->service->balanceSheet(company_id(), $asOf);

        return $this->csv("balance_sheet_{$asOf}.csv", ['Code','Name','Type','Debit','Credit','Net'], $rep['rows'], function($r){
            return [$r['code'],$r['name'],$r['account_type'],$r['debit'],$r['credit'],$r['net']];
        });
    }

    private function csv(string $filename, array $headers, array $rows, callable $map): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows, $map) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $r) fputcsv($out, $map($r));
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
