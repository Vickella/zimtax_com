<?php

namespace App\Services\Inventory;

use App\Models\StockLedgerEntry;
use Illuminate\Support\Facades\DB;

class InventoryPostingService
{
    /**
     * Post stock for a Sales Invoice (stock OUT).
     * Expects $invoice->lines with item_id, warehouse_id, qty, rate.
     */
    public function postSalesInvoice($invoice): void
    {
        if ($invoice->status !== 'SUBMITTED') {
            return;
        }

        DB::transaction(function () use ($invoice) {
            // prevent duplicates
            $exists = StockLedgerEntry::where('voucher_type', 'SalesInvoice')
                ->where('voucher_id', $invoice->id)
                ->exists();

            if ($exists) return;

            foreach ($invoice->lines as $line) {
                if (!$line->warehouse_id) continue; // service/no warehouse

                $qtyOut = (float) $line->qty * -1;

                StockLedgerEntry::create([
                    'company_id' => $invoice->company_id,
                    'item_id' => $line->item_id,
                    'warehouse_id' => $line->warehouse_id,
                    'posting_date' => $invoice->posting_date,
                    'posting_time' => now()->format('H:i:s'),
                    'voucher_type' => 'SalesInvoice',
                    'voucher_id' => $invoice->id,
                    'qty' => $qtyOut,
                    'unit_cost' => null, // optional: implement costing later
                    'value_change' => null,
                    'running_qty' => null,
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * Post stock for a Purchase Invoice (stock IN).
     */
    public function postPurchaseInvoice($invoice): void
    {
        if ($invoice->status !== 'SUBMITTED') {
            return;
        }

        DB::transaction(function () use ($invoice) {
            $exists = StockLedgerEntry::where('voucher_type', 'PurchaseInvoice')
                ->where('voucher_id', $invoice->id)
                ->exists();

            if ($exists) return;

            foreach ($invoice->lines as $line) {
                if (!$line->warehouse_id) continue;

                $qtyIn = (float) $line->qty;

                StockLedgerEntry::create([
                    'company_id' => $invoice->company_id,
                    'item_id' => $line->item_id,
                    'warehouse_id' => $line->warehouse_id,
                    'posting_date' => $invoice->posting_date,
                    'posting_time' => now()->format('H:i:s'),
                    'voucher_type' => 'PurchaseInvoice',
                    'voucher_id' => $invoice->id,
                    'qty' => $qtyIn,
                    'unit_cost' => (float) $line->rate, // basic
                    'value_change' => round($qtyIn * (float) $line->rate, 2),
                    'running_qty' => null,
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * Reverse stock entries on cancel.
     * This keeps ledger append-only (accounting-grade).
     */
    public function reverseVoucher(string $voucherType, int $voucherId, int $companyId): void
    {
        DB::transaction(function () use ($voucherType, $voucherId, $companyId) {
            $rows = StockLedgerEntry::where('company_id', $companyId)
                ->where('voucher_type', $voucherType)
                ->where('voucher_id', $voucherId)
                ->get();

            if ($rows->isEmpty()) return;

            // prevent double reversals
            $hasReversal = StockLedgerEntry::where('company_id', $companyId)
                ->where('voucher_type', $voucherType.'-REVERSAL')
                ->where('voucher_id', $voucherId)
                ->exists();

            if ($hasReversal) return;

            foreach ($rows as $r) {
                StockLedgerEntry::create([
                    'company_id' => $companyId,
                    'item_id' => $r->item_id,
                    'warehouse_id' => $r->warehouse_id,
                    'posting_date' => now()->toDateString(),
                    'posting_time' => now()->format('H:i:s'),
                    'voucher_type' => $voucherType.'-REVERSAL',
                    'voucher_id' => $voucherId,
                    'qty' => ((float) $r->qty) * -1,
                    'unit_cost' => $r->unit_cost,
                    'value_change' => $r->value_change ? ((float)$r->value_change) * -1 : null,
                    'running_qty' => null,
                    'created_at' => now(),
                ]);
            }
        });
    }
}
