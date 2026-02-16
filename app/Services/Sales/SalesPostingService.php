<?php

namespace App\Services\Sales;

use App\Models\{SalesInvoice, StockLedgerEntry, Item};
use App\Services\Accounting\{CoaResolver, JournalPostingService};
use Illuminate\Support\Facades\DB;

class SalesPostingService
{
    public function __construct(
        private CoaResolver $coa,
        private JournalPostingService $posting
    ) {}

    public function submit(SalesInvoice $invoice, int $userId): void
    {
        if ($invoice->status !== 'DRAFT') {
            throw new \RuntimeException('Only DRAFT invoices can be submitted.');
        }

        $invoice->load(['lines', 'lines.item']);

        DB::transaction(function () use ($invoice, $userId) {

            $companyId = (int)$invoice->company_id;

            // Required accounts (your config codes)
            $ar      = $this->coa->require($companyId, '1200'); // Accounts Receivable
            $sales   = $this->coa->require($companyId, '4100'); // Sales Revenue
            $vatOut  = $this->coa->require($companyId, '2200'); // VAT Payable (Output VAT)

            // Optional but required for proper stock accounting in your system design
            $cogsAcc = $this->coa->require($companyId, '5100'); // COGS
            $invAcc  = $this->coa->require($companyId, '1300'); // Inventory

            $postingDate = $invoice->posting_date->format('Y-m-d');
            $currency = $invoice->currency;
            $rate = (float)($invoice->exchange_rate ?? 1);

            // Create Journal Entry
            $je = $this->posting->createPostedJournal(
                $companyId,
                $postingDate,
                'Sales Invoice ' . $invoice->invoice_no,
                'SalesInvoice',
                (int)$invoice->id,
                $currency,
                $rate,
                $userId
            );

            // DR AR total
            $this->posting->addLine(
                $je,
                $ar->id,
                'AR - ' . $invoice->invoice_no,
                (float)$invoice->total,
                0.0,
                'CUSTOMER',
                (int)$invoice->customer_id
            );

            // CR Sales subtotal
            $this->posting->addLine(
                $je,
                $sales->id,
                'Sales - ' . $invoice->invoice_no,
                0.0,
                (float)$invoice->subtotal
            );

            // CR VAT Output
            if ((float)$invoice->vat_amount > 0) {
                $this->posting->addLine(
                    $je,
                    $vatOut->id,
                    'VAT Output - ' . $invoice->invoice_no,
                    0.0,
                    (float)$invoice->vat_amount
                );
            }

            // Stock + COGS/Inventory posting per stock line
            foreach ($invoice->lines as $line) {
                $item = $line->item ?? Item::query()
                    ->where('company_id', $companyId)
                    ->where('id', $line->item_id)
                    ->first();

                if (!$item) continue;

                if (($item->item_type ?? null) === 'STOCK' && $line->warehouse_id) {

                    $costPrice = (float)($item->cost_price ?? 0);
                    $qty = (float)$line->qty;
                    $cost = $costPrice > 0 ? ($qty * $costPrice) : 0;

                    // Stock ledger: OUT
                    StockLedgerEntry::create([
                        'company_id' => $companyId,
                        'item_id' => (int)$line->item_id,
                        'warehouse_id' => (int)$line->warehouse_id,
                        'posting_date' => $postingDate,
                        'posting_time' => now()->format('H:i:s'),
                        'voucher_type' => 'SalesInvoice',
                        'voucher_id' => (int)$invoice->id,
                        'qty' => bcmul((string)$qty, '-1', 4),
                        'unit_cost' => $costPrice ?: null,
                        'value_change' => $cost > 0 ? ($cost * -1) : null,
                    ]);

                    // GL: DR COGS, CR Inventory (only if we have cost)
                    if ($cost > 0) {
                        $this->posting->addLine(
                            $je,
                            $cogsAcc->id,
                            'COGS - ' . $invoice->invoice_no,
                            $cost,
                            0.0
                        );

                        $this->posting->addLine(
                            $je,
                            $invAcc->id,
                            'Inventory - ' . $invoice->invoice_no,
                            0.0,
                            $cost
                        );
                    }
                }
            }

            $this->posting->assertBalanced($je);

            // Mark invoice submitted
            $invoice->status = 'SUBMITTED';
            $invoice->submitted_by = $userId;
            $invoice->submitted_at = now();
            $invoice->save();
        });
    }
}
