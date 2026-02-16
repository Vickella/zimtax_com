<?php

namespace App\Services\Purchases;

use App\Models\{PurchaseInvoice, PurchaseInvoiceLine, Supplier, StockLedgerEntry, Item};
use App\Services\Accounting\{CoaResolver, JournalPostingService};
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceService
{
    public function __construct(
        private PurchaseTaxCalculator $calc,
        private CoaResolver $coa,
        private JournalPostingService $posting
    ) {}

    public function createDraft(int $companyId, int $userId, array $data): PurchaseInvoice
    {
        Supplier::query()->where('company_id', $companyId)->findOrFail($data['supplier_id']);

        $postingDate = $data['posting_date'];
        $computed = $this->calc->compute($companyId, $postingDate, $data['lines'] ?? []);

        if (count($computed['lines']) < 1) {
            abort(422, 'At least 1 valid line is required.');
        }

        $invoice = PurchaseInvoice::create([
            'company_id' => $companyId,
            'invoice_no' => $data['invoice_no'] ?? $this->nextInvoiceNo($companyId, $postingDate),
            'supplier_id' => $data['supplier_id'],
            'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
            'posting_date' => $postingDate,
            'due_date' => $data['due_date'] ?? null,
            'currency' => $data['currency'],
            'exchange_rate' => $data['exchange_rate'] ?? 1,
            'status' => 'DRAFT',
            'subtotal' => $computed['subtotal'],
            'vat_amount' => $computed['vat_amount'],
            'total' => $computed['total'],
            'remarks' => $data['remarks'] ?? null,
            'created_by' => $userId,
        ]);

        $invoice->lines()->createMany($computed['lines']);

        return $invoice->fresh(['supplier','lines.item','lines.warehouse']);
    }

    public function updateDraft(PurchaseInvoice $invoice, array $data): void
    {
        $companyId = (int)$invoice->company_id;

        Supplier::query()->where('company_id', $companyId)->findOrFail($data['supplier_id']);

        $postingDate = $data['posting_date'];
        $computed = $this->calc->compute($companyId, $postingDate, $data['lines'] ?? []);

        if (count($computed['lines']) < 1) {
            abort(422, 'At least 1 valid line is required.');
        }

        $invoice->update([
            'supplier_id' => $data['supplier_id'],
            'supplier_invoice_no' => $data['supplier_invoice_no'] ?? $invoice->supplier_invoice_no,
            'posting_date' => $postingDate,
            'due_date' => $data['due_date'] ?? null,
            'currency' => $data['currency'],
            'exchange_rate' => $data['exchange_rate'] ?? 1,
            'subtotal' => $computed['subtotal'],
            'vat_amount' => $computed['vat_amount'],
            'total' => $computed['total'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        PurchaseInvoiceLine::query()->where('purchase_invoice_id', $invoice->id)->delete();
        $invoice->lines()->createMany($computed['lines']);
    }

    public function submit(PurchaseInvoice $invoice, int $userId): void
    {
        if ($invoice->status !== 'DRAFT') abort(403, 'Only DRAFT invoices can be submitted.');

        $invoice->load(['lines', 'lines.item']);

        $companyId = (int)$invoice->company_id;
        $postingDate = $invoice->posting_date->format('Y-m-d');

        // IMPORTANT: include vat_rate in recompute input
        $computed = $this->calc->compute(
            $companyId,
            $postingDate,
            $invoice->lines->map(function($l){
                return [
                    'item_id' => $l->item_id,
                    'warehouse_id' => $l->warehouse_id,
                    'qty' => $l->qty,
                    'rate' => $l->rate,
                    'vat_rate' => $l->vat_rate, // KEEP VAT
                    'description' => $l->description,
                ];
            })->toArray()
        );

        DB::transaction(function () use ($invoice, $computed, $userId, $companyId, $postingDate) {

            // rewrite stored lines to computed ones (server-truth)
            PurchaseInvoiceLine::query()->where('purchase_invoice_id', $invoice->id)->delete();
            $invoice->lines()->createMany($computed['lines']);

            $invoice->update([
                'subtotal' => $computed['subtotal'],
                'vat_amount' => $computed['vat_amount'],
                'total' => $computed['total'],
                'status' => 'SUBMITTED',
                'submitted_by' => $userId,
                'submitted_at' => now(),
            ]);

            $invoice->load(['lines', 'lines.item']);

            // Resolve required accounts (your config codes)
            $ap     = $this->coa->require($companyId, '2100'); // Accounts Payable
            $vatIn  = $this->coa->require($companyId, '2210'); // VAT Receivable (Input VAT)
            $invAcc = $this->coa->require($companyId, '1300'); // Inventory
            $expAcc = $this->coa->require($companyId, '6000'); // Operating expenses (fallback)

            $currency = $invoice->currency;
            $rate = (float)($invoice->exchange_rate ?? 1);

            // Create JE
            $je = $this->posting->createPostedJournal(
                $companyId,
                $postingDate,
                'Purchase Invoice ' . $invoice->invoice_no,
                'PurchaseInvoice',
                (int)$invoice->id,
                $currency,
                $rate,
                $userId
            );

            // CR AP total
            $this->posting->addLine(
                $je,
                $ap->id,
                'AP - ' . $invoice->invoice_no,
                0.0,
                (float)$invoice->total,
                'SUPPLIER',
                (int)$invoice->supplier_id
            );

            // DR VAT Receivable
            if ((float)$invoice->vat_amount > 0) {
                $this->posting->addLine(
                    $je,
                    $vatIn->id,
                    'VAT Input - ' . $invoice->invoice_no,
                    (float)$invoice->vat_amount,
                    0.0
                );
            }

            // DR Inventory / Expenses subtotal split
            // (VAT already separated above, so subtotal is base amount)
            $invSubtotal = 0.0;
            $expSubtotal = 0.0;

            foreach ($invoice->lines as $line) {
                $item = $line->item;
                $amount = (float)$line->amount;

                if ($item && strtoupper((string)($item->item_type ?? '')) === 'STOCK') {
                    $invSubtotal += $amount;

                    if ($line->warehouse_id) {
                        $unitCost = (float)($line->rate ?? 0);

                        StockLedgerEntry::create([
                            'company_id' => $companyId,
                            'item_id' => (int)$line->item_id,
                            'warehouse_id' => (int)$line->warehouse_id,
                            'posting_date' => $postingDate,
                            'posting_time' => now()->format('H:i:s'),
                            'voucher_type' => 'PurchaseInvoice',
                            'voucher_id' => (int)$invoice->id,
                            'qty' => bcmul((string)$line->qty, '1', 4),
                            'unit_cost' => $unitCost > 0 ? $unitCost : null,
                            'value_change' => $unitCost > 0 ? ((float)$line->qty * $unitCost) : null,
                        ]);
                    }
                } else {
                    $expSubtotal += $amount;
                }
            }

            if ($invSubtotal > 0) {
                $this->posting->addLine(
                    $je,
                    $invAcc->id,
                    'Inventory - ' . $invoice->invoice_no,
                    $invSubtotal,
                    0.0
                );
            }

            if ($expSubtotal > 0) {
                $this->posting->addLine(
                    $je,
                    $expAcc->id,
                    'Purchases/Expenses - ' . $invoice->invoice_no,
                    $expSubtotal,
                    0.0
                );
            }

            $this->posting->assertBalanced($je);
        });
    }

    public function cancel(PurchaseInvoice $invoice, int $userId): void
    {
        if (!in_array($invoice->status, ['SUBMITTED','DRAFT'], true)) abort(403, 'Invalid status.');

        $invoice->update(['status' => 'CANCELLED']);

        // Next: reverse JE + reverse stock ledger (we can implement when you add cancellation rules)
    }

    private function nextInvoiceNo(int $companyId, string $postingDate): string
    {
        $ym = now()->parse($postingDate)->format('Ym');

        $last = PurchaseInvoice::query()
            ->where('company_id', $companyId)
            ->where('invoice_no', 'like', "PI-$ym-%")
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $seq = (int)end($parts) + 1;
        }

        return sprintf("PI-%s-%06d", $ym, $seq);
    }
}
