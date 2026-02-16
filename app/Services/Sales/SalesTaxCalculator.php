<?php

namespace App\Services\Sales;

use App\Models\Item;
use App\Services\Tax\TaxRateResolver;

class SalesTaxCalculator
{
    public function __construct(private TaxRateResolver $tax) {}

    public function compute(int $companyId, string $postingDate, array $lines): array
    {
        $subtotal = 0.0;
        $vatTotal = 0.0;
        $out = [];

        foreach ($lines as $l) {
            $itemId = (int)($l['item_id'] ?? 0);
            $qty    = (float)($l['qty'] ?? 0);
            $rate   = (float)($l['rate'] ?? 0);

            if ($itemId <= 0 || $qty <= 0) continue;

            $item = Item::query()
                ->forCompany($companyId)
                ->where('is_active', 1)
                ->findOrFail($itemId);

            $amount = round($qty * $rate, 2);

            $vatRate = $this->tax->vatRate($companyId, $item->vat_category, $postingDate);
            $vatAmt  = round($amount * ($vatRate / 100), 2);

            $subtotal += $amount;
            $vatTotal += $vatAmt;

            $out[] = [
                'item_id' => $itemId,
                'warehouse_id' => $l['warehouse_id'] ?: null,
                'qty' => $qty,
                'rate' => $rate,
                'amount' => $amount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmt,
                'description' => $l['description'] ?? null,
            ];
        }

        $subtotal = round($subtotal, 2);
        $vatTotal = round($vatTotal, 2);

        return [
            'subtotal' => $subtotal,
            'vat_amount' => $vatTotal,
            'total' => round($subtotal + $vatTotal, 2),
            'lines' => $out,
        ];
    }
}
