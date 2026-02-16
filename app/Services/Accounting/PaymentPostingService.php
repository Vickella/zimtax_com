<?php

namespace App\Services\Accounting;

use App\Models\{Payment, PaymentAllocation, BankAccount, ChartOfAccount, JournalEntry};
use App\Services\Numbers\NumberSeries;
use Illuminate\Support\Facades\DB;

class PaymentPostingService
{
    // You MUST align these codes with your required COA setup.
    // Replace codes if your COA differs.
    private const AR_CODE = '1200'; // Accounts Receivable
    private const AP_CODE = '2100'; // Accounts Payable

    public function createDraft(int $companyId, int $userId, array $data): Payment
    {
        $no = NumberSeries::next('PAY', $companyId, 'payments', 'payment_no');

        $payment = Payment::create([
            'company_id' => $companyId,
            'payment_no' => $no,
            'payment_type' => $data['payment_type'],
            'party_type' => $data['party_type'],
            'party_id' => $data['party_id'],
            'bank_account_id' => $data['bank_account_id'],
            'posting_date' => $data['posting_date'],
            'currency' => $data['currency'],
            'exchange_rate' => $data['exchange_rate'],
            'amount' => $data['amount'],
            'reference' => $data['reference'] ?? null,
            'status' => 'DRAFT',
            'created_by' => $userId,
        ]);

        // optional allocations
        if (!empty($data['allocations'])) {
            $sumAlloc = 0;
            foreach ($data['allocations'] as $a) {
                $sumAlloc += (float)$a['allocated_amount'];
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'reference_type' => $a['reference_type'],
                    'reference_id' => $a['reference_id'],
                    'allocated_amount' => $a['allocated_amount'],
                ]);
            }

            // don't allow over-allocation
            if (round($sumAlloc, 2) > round((float)$payment->amount, 2)) {
                abort(422, 'Allocated amount exceeds payment amount.');
            }
        }

        return $payment->load('allocations','bankAccount');
    }

    public function submit(Payment $payment, int $userId, JournalPostingService $journalService): Payment
    {
        if ($payment->status !== 'DRAFT') abort(403, 'Only DRAFT payments can be submitted.');

        $payment->load('allocations','bankAccount.glAccount');

        $bank = $payment->bankAccount;
        if (!$bank || !$bank->is_active) abort(422, 'Invalid bank account.');

        $bankGl = $bank->glAccount;
        if (!$bankGl || !$bankGl->is_active) abort(422, 'Bank GL account is not active.');

        $partyControl = $this->resolvePartyControlAccount($payment->company_id, $payment->party_type);

        // Receipt: Dr Bank, Cr AR (customer)
        // Payment: Cr Bank, Dr AP (supplier) / Dr Salary payable (employee) – simplified to control account
        $lines = [];

        if ($payment->payment_type === 'RECEIPT') {
            $lines[] = [
                'account_id' => $bankGl->id,
                'description' => 'Receipt',
                'debit' => (float)$payment->amount,
                'credit' => 0,
                'party_type' => 'NONE',
                'party_id' => null,
            ];
            $lines[] = [
                'account_id' => $partyControl->id,
                'description' => 'Customer receipt',
                'debit' => 0,
                'credit' => (float)$payment->amount,
                'party_type' => $payment->party_type,
                'party_id' => $payment->party_id,
            ];
        } else {
            $lines[] = [
                'account_id' => $partyControl->id,
                'description' => 'Payment',
                'debit' => (float)$payment->amount,
                'credit' => 0,
                'party_type' => $payment->party_type,
                'party_id' => $payment->party_id,
            ];
            $lines[] = [
                'account_id' => $bankGl->id,
                'description' => 'Bank payment',
                'debit' => 0,
                'credit' => (float)$payment->amount,
                'party_type' => 'NONE',
                'party_id' => null,
            ];
        }

        // create JE and post
        $jeNo = NumberSeries::next('JE', $payment->company_id, 'journal_entries', 'entry_no');

        $journal = $journalService->createDraft(
            $payment->company_id,
            $userId,
            [
                'posting_date' => $payment->posting_date->format('Y-m-d'),
                'memo' => "{$payment->payment_type} {$payment->payment_no}",
                'currency' => $payment->currency,
                'exchange_rate' => $payment->exchange_rate,
                'lines' => $lines,
            ],
            $jeNo
        );

        $journal->update([
            'source_type' => 'Payment',
            'source_id' => $payment->id,
        ]);

        $journalService->post($journal, $userId);

        $payment->update([
            'status' => 'SUBMITTED',
            'submitted_by' => $userId,
            'submitted_at' => now(),
        ]);

        return $payment->fresh()->load('allocations','bankAccount');
    }

    public function cancel(Payment $payment): void
    {
        if ($payment->status !== 'DRAFT') abort(403, 'Only DRAFT payments can be cancelled.');
        $payment->update(['status' => 'CANCELLED']);
    }

    public function reverse(Payment $payment, int $userId, JournalPostingService $journalService): JournalEntry
    {
        if ($payment->status !== 'SUBMITTED') abort(403, 'Only SUBMITTED payments can be reversed.');

        $journal = JournalEntry::query()
            ->where('company_id', $payment->company_id)
            ->where('source_type', 'Payment')
            ->where('source_id', $payment->id)
            ->where('status', 'POSTED')
            ->firstOrFail();

        $revNo = NumberSeries::next('JE', $payment->company_id, 'journal_entries', 'entry_no');
        $rev = $journalService->reverse($journal, $userId, $revNo);

        $payment->update(['status' => 'REVERSED']);

        return $rev;
    }

    private function resolvePartyControlAccount(int $companyId, string $partyType): ChartOfAccount
    {
        $code = match ($partyType) {
            'CUSTOMER' => self::AR_CODE,
            'SUPPLIER' => self::AP_CODE,
            'EMPLOYEE' => self::AP_CODE, // if you later add Net Salary Payable, replace this
            default => self::AP_CODE,
        };

        return ChartOfAccount::query()
            ->forCompany($companyId)
            ->where('code', $code)
            ->where('is_active', 1)
            ->firstOrFail();
    }
}
