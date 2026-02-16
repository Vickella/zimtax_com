<?php

namespace App\Services\Purchases;

use App\Models\Item;

class PurchaseTaxCalculator
{
    public function compute(int $companyId, string $postingDate, array $lines): array
    {
        $outLines = [];
        $subtotal = 0.0;
        $vatTotal = 0.0;

        $defaultVat = (float) config('tax.vat_rate_default', 15);

        foreach ($lines as $l) {
            $itemId = (int)($l['item_id'] ?? 0);
            $qty = (float)($l['qty'] ?? 0);
            $rate = (float)($l['rate'] ?? 0);

            if ($itemId <= 0 || $qty <= 0) continue;

            $item = Item::query()
                ->where('company_id', $companyId)
                ->where('id', $itemId)
                ->first();

            if (!$item) continue;

            $amount = $qty * $rate;

            // VAT rate priority: line -> item -> default
            $vatRate = isset($l['vat_rate']) && $l['vat_rate'] !== '' ? (float)$l['vat_rate'] : null;

            if ($vatRate === null) {
                // if your items table has vat_rate, use it
                if (isset($item->vat_rate) && $item->vat_rate !== null) {
                    $vatRate = (float)$item->vat_rate;
                } else {
                    // fallback based on vat_category if you have it
                    // STANDARD => default VAT, ZERO/EXEMPT => 0
                    $cat = strtoupper((string)($item->vat_category ?? 'STANDARD'));
                    $vatRate = in_array($cat, ['ZERO', 'ZERO_RATED', 'EXEMPT'], true) ? 0.0 : $defaultVat;
                }
            }

            $vatAmt = $amount * ($vatRate / 100);

            $subtotal += $amount;
            $vatTotal += $vatAmt;

            $outLines[] = [
                'item_id' => $itemId,
                'warehouse_id' => $l['warehouse_id'] ?? null,
                'qty' => $qty,
                'rate' => $rate,
                'amount' => round($amount, 2),
                'vat_rate' => $vatRate,
                'vat_amount' => round($vatAmt, 2),
                'description' => $l['description'] ?? null,
            ];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'vat_amount' => round($vatTotal, 2),
            'total' => round($subtotal + $vatTotal, 2),
            'lines' => $outLines,
        ];
    }
}
