<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FiscalPeriodService
{
    public function ensureMonthlyPeriod(int $companyId, int $year, int $month): int
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));
        $name  = sprintf('%04d-%02d', $year, $month);

        $existing = DB::table('fiscal_periods')
            ->where('company_id', $companyId)
            ->where('period_type', 'MONTH')
            ->where('start_date', $start)
            ->where('end_date', $end)
            ->value('id');

        if ($existing) return (int) $existing;

        DB::table('fiscal_periods')->insert([
            'company_id'   => $companyId,
            'name'         => $name,
            'period_type'  => 'MONTH',
            'start_date'   => $start,
            'end_date'     => $end,
            'is_closed'    => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }
}
