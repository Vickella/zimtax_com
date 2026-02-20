<?php

namespace App\Services\Tax;

use Illuminate\Support\Facades\DB;

class GlTaxRepository
{
    public function sumByAccountCode(int $companyId, string $accountCode, string $start, string $end): array
    {
        $row = DB::table('gl_entries as ge')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'ge.account_id')
            ->join('journal_entries as je', 'je.id', '=', 'ge.journal_entry_id')
            ->where('ge.company_id', $companyId)
            ->where('coa.code', $accountCode)
            ->whereBetween('ge.posting_date', [$start, $end])
            ->where('je.status', 'POSTED')
            ->selectRaw('COALESCE(SUM(ge.debit),0) as debit_sum, COALESCE(SUM(ge.credit),0) as credit_sum')
            ->first();

        return [
            'debit' => (float)($row->debit_sum ?? 0),
            'credit' => (float)($row->credit_sum ?? 0),
        ];
    }

    public function profitLossTotals(int $companyId, string $start, string $end): array
    {
        // Uses chart_of_accounts.type = INCOME / EXPENSE (your schema)
        $rows = DB::table('gl_entries as ge')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'ge.account_id')
            ->join('journal_entries as je', 'je.id', '=', 'ge.journal_entry_id')
            ->where('ge.company_id', $companyId)
            ->whereBetween('ge.posting_date', [$start, $end])
            ->where('je.status', 'POSTED')
            ->whereIn('coa.type', ['INCOME','EXPENSE'])
            ->selectRaw('coa.type as account_type, COALESCE(SUM(ge.debit),0) as debit_sum, COALESCE(SUM(ge.credit),0) as credit_sum')
            ->groupBy('coa.type')
            ->get();

        $income = 0.0;
        $expense = 0.0;

        foreach ($rows as $r) {
            // INCOME normally credits > debits
            if ($r->account_type === 'INCOME') {
                $income = (float)$r->credit_sum - (float)$r->debit_sum;
            }
            // EXPENSE normally debits > credits
            if ($r->account_type === 'EXPENSE') {
                $expense = (float)$r->debit_sum - (float)$r->credit_sum;
            }
        }

        return ['income' => $income, 'expense' => $expense];
    }
}
