<?php

namespace App\Services\Accounting;

use App\Models\FiscalPeriod;
use Carbon\Carbon;

class FiscalPeriodService
{
    public function assertOpen(int $companyId, string|\DateTimeInterface $date): void
    {
        $d = Carbon::parse($date)->toDateString();

        $period = FiscalPeriod::query()
            ->where('company_id', $companyId)
            ->whereDate('start_date', '<=', $d)
            ->whereDate('end_date', '>=', $d)
            ->orderBy('start_date', 'desc')
            ->first();

        abort_if(! $period, 409, "No fiscal period found covering {$d}.");

        abort_if((int) $period->is_closed === 1, 409, "Fiscal period {$period->name} is closed. Posting blocked.");
    }
}
