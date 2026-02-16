<?php

namespace App\Services\Purchases;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;

class PurchasePostingService
{
    public function post(PurchaseInvoice $inv, int $userId): void
    {
        DB::transaction(function () use ($inv, $userId) {

            if ($inv->status !== 'SUBMITTED') {
                throw new \RuntimeException('Purchase invoice must be SUBMITTED before posting.');
            }

            // Accounts
            $ap = $this->coa($inv->company_id, '2100');  // Accounts Payable (control)
            $invAcc = $this->coa($inv->company_id, '1300'); // Inventory (simple baseline)
            $vatInput = $this->coa($inv->company_id, '1215'); // VAT Input (Recoverable)

            $je = JournalEntry::create([
                'company_id' => $inv->company_id,
                'entry_no' => $this->nextJeNo($inv->company_id, $inv->posting_date),
                'posting_date' => $inv->posting_date,
                'memo' => "Purchase Invoice {$inv->invoice_no}",
                'status' => 'POSTED',
                'source_type' => 'PurchaseInvoice',
                'source_id' => $inv->id,
                'currency' => $inv->currency,
                'exchange_rate' => $inv->exchange_rate ?? 1,
                'created_by' => $userId,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            // Dr Inventory (subtotal)
            JournalLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $invAcc->id,
                'description' => "Inventory / Purchases for {$inv->invoice_no}",
                'debit' => $inv->subtotal,
                'credit' => 0,
                'party_type' => 'NONE',
            ]);

            // Dr VAT Input (input VAT)
            if ((float)$inv->vat_amount > 0) {
                JournalLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $vatInput->id,
                    'description' => "VAT Input for {$inv->invoice_no}",
                    'debit' => $inv->vat_amount,
                    'credit' => 0,
                    'party_type' => 'NONE',
                ]);
            }

            // Cr Accounts Payable (gross)
            JournalLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $ap->id,
                'description' => "Payable to supplier for {$inv->invoice_no}",
                'debit' => 0,
                'credit' => $inv->total,
                'party_type' => 'SUPPLIER',
                'party_id' => $inv->supplier_id,
            ]);
        });
    }

    private function coa(int $companyId, string $code): ChartOfAccount
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->where('is_active', 1)
            ->firstOrFail();
    }

    private function nextJeNo(int $companyId, string $postingDate): string
    {
        $ym = now()->parse($postingDate)->format('Ym');

        $last = JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('entry_no', 'like', "JE-{$ym}-%")
            ->lockForUpdate()
            ->orderByDesc('entry_no')
            ->value('entry_no');

        $next = $last ? ((int)substr($last, -6)) + 1 : 1;
        return sprintf("JE-%s-%06d", $ym, $next);
    }
}
