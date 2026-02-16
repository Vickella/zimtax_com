<?php

namespace App\Services\Accounting;

use App\Models\{GLEntry, ChartOfAccount};
use Illuminate\Support\Facades\DB;

class AccountingReportService
{
    public function trialBalance(int $companyId, string $from, string $to): array
    {
        $rows = DB::table('gl_entries as gl')
            ->join('chart_of_accounts as a', 'a.id', '=', 'gl.account_id')
            ->where('gl.company_id', $companyId)
            ->whereBetween('gl.posting_date', [$from, $to])
            ->groupBy('gl.account_id','a.code','a.name','a.type')
            ->orderBy('a.code')
            ->selectRaw('a.code, a.name, a.type, SUM(gl.debit) as debit, SUM(gl.credit) as credit, SUM(gl.debit - gl.credit) as net')
            ->get();

        return $rows->map(fn($r) => (array)$r)->all();
    }

    public function profitLoss(int $companyId, string $from, string $to): array
    {
        $rows = DB::table('gl_entries as gl')
            ->join('chart_of_accounts as a', 'a.id', '=', 'gl.account_id')
            ->where('gl.company_id', $companyId)
            ->whereBetween('gl.posting_date', [$from, $to])
            ->whereIn('a.type', ['INCOME','EXPENSE'])
            ->groupBy('gl.account_id','a.code','a.name','a.type')
            ->orderBy('a.code')
            ->selectRaw('a.code, a.name, a.type, SUM(gl.debit) as debit, SUM(gl.credit) as credit, SUM(gl.credit - gl.debit) as balance')
            ->get();

        $income = $rows->where('type','INCOME')->sum('balance');
        $expense = $rows->where('type','EXPENSE')->sum('balance');
        $profit = round((float)$income - (float)$expense, 2);

        return [
            'rows' => $rows->map(fn($r)=>(array)$r)->all(),
            'total_income' => round((float)$income, 2),
            'total_expenses' => round((float)$expense, 2),
            'net_profit' => $profit,
        ];
    }

    public function balanceSheet(int $companyId, string $asOf): array
    {
        // Ending balances up to asOf (inclusive)
        $rows = DB::table('gl_entries as gl')
            ->join('chart_of_accounts as a', 'a.id', '=', 'gl.account_id')
            ->where('gl.company_id', $companyId)
            ->where('gl.posting_date', '<=', $asOf)
            ->whereIn('a.type', ['ASSET','LIABILITY','EQUITY'])
            ->groupBy('gl.account_id','a.code','a.name','a.type')
            ->orderBy('a.code')
            ->selectRaw('a.code, a.name, a.type, SUM(gl.debit) as debit, SUM(gl.credit) as credit, SUM(gl.debit - gl.credit) as net')
            ->get();

        $assets = $rows->where('type','ASSET')->sum('net');
        // liabilities/equity usually credit balances => net may be negative; we present absolute credit-basis
        $liabs = $rows->where('type','LIABILITY')->sum('net');
        $equity = $rows->where('type','EQUITY')->sum('net');

        // Convert to presentation:
        // Assets = net (Dr - Cr)
        // Liabilities/Equity = (Cr - Dr) => negative of net
        $liabsDisplay = round((float)(-$liabs), 2);
        $equityDisplay = round((float)(-$equity), 2);

        return [
            'rows' => $rows->map(fn($r)=>(array)$r)->all(),
            'total_assets' => round((float)$assets, 2),
            'total_liabilities' => $liabsDisplay,
            'total_equity' => $equityDisplay,
            'liabilities_plus_equity' => round($liabsDisplay + $equityDisplay, 2),
        ];
    }

    public function generalLedger(int $companyId, ?int $accountId, string $from, string $to): array
    {
        $q = DB::table('gl_entries as gl')
            ->join('chart_of_accounts as a', 'a.id', '=', 'gl.account_id')
            ->where('gl.company_id', $companyId)
            ->whereBetween('gl.posting_date', [$from, $to])
            ->orderBy('gl.posting_date')
            ->orderBy('gl.id')
            ->selectRaw('gl.posting_date, a.code, a.name, gl.debit, gl.credit, gl.currency, gl.journal_entry_id, gl.party_type, gl.party_id');

        if ($accountId) $q->where('gl.account_id', $accountId);

        return $q->get()->map(fn($r)=>(array)$r)->all();
    }
}
