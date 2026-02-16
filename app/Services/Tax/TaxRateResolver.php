<?php

namespace App\Services\Tax;

use App\Models\TaxRate;
use Carbon\Carbon;

class TaxRateResolver
{
    public function vatRate(int $companyId, ?string $vatCategory, string $postingDate): float
    {
        if (!$vatCategory) return 0.0;

        $d = Carbon::parse($postingDate)->toDateString();

        $row = TaxRate::query()
            ->where('company_id', $companyId)
            ->where('tax_type', 'VAT')
            ->where('code', $vatCategory)
            ->where('effective_from', '<=', $d)
            ->where(function ($q) use ($d) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $d);
            })
            ->where('is_active', 1)
            ->orderByDesc('effective_from')
            ->first();

        return $row ? (float)$row->rate : 0.0;
    }
}
