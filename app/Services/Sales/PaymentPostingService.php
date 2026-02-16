<?php

namespace App\Services\Sales;

use App\Models\{Payment, PaymentAllocation, BankAccount, SalesInvoice, JournalEntry, JournalLine, GLEntry, ChartOfAccount};
use App\Services\Numbers\NumberSeries;
use Illuminate\Support\Facades\DB;

class PaymentPostingService
{
    /**
     * REQUIRED COA:
     * - Accounts Receivable code: 1100-AR
     * Bank GL account comes from bank_accounts.gl_account_id
     */
    public function createReceipt(array $payload, int $companyId, int $userId): Payment
    {
        return DB::transaction(function () use ($payload, $companyId, $userId) {

            $payNo = NumberSeries::next('PR', $companyId, 'payments', 'payment_no');

            $payment = Payment::create([
                'company_id' => $companyId,
                'payment_no' => $payNo,
                'payment_type' => 'RECEIPT',
                'party_type' => 'CUSTOMER',
                'party_id' => $payload['customer_id'],
                'bank_account_id' => $payload['bank_account_id'],
                'posting_date' => $payload['posting_date'],
                'currency' => $payload['currency'],
                'exchange_rate' => $payload['exchange_rate'] ?? 1,
                'amount' => $payload['amount'],
                'reference' => $payload['reference'] ?? null,
                'status' => 'SUBMITTED',
                'created_by' => $userId,
                'submitted_by' => $userId,
                'submitted_at' => now(),
            ]);

            // allocations
            $allocTotal = 0;
            foreach ($payload['allocations'] ?? [] as $a) {
                if (($a['allocated_amount'] ?? 0) <= 0) continue;

                $invoice = SalesInvoice::query()
                    ->where('company_id', $companyId)
                    ->where('id', $a['invoice_id'])
                    ->where('status','SUBMITTED')
                    ->firstOrFail();

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'reference_type' => 'SALES_INVOICE',
                    'reference_id' => $invoice->id,
                    'allocated_amount' => $a['allocated_amount'],
                ]);

                $allocTotal += (float)$a['allocated_amount'];
            }

            // Journal entry for receipt
            $bank = BankAccount::query()
                ->where('company_id',$companyId)
                ->where('id',$payment->bank_account_id)
                ->firstOrFail();

            $ar = ChartOfAccount::query()
                ->where('company_id',$companyId)->where('code','1100-AR')->where('is_active',1)
                ->firstOrFail();

            $jeNo = NumberSeries::next('JE', $companyId, 'journal_entries', 'entry_no');

            $je = JournalEntry::create([
                'company_id' => $companyId,
                'entry_no' => $jeNo,
                'posting_date' => $payment->posting_date,
                'memo' => 'Receipt ' . $payment->payment_no,
                'status' => 'POSTED',
                'source_type' => 'Payment',
                'source_id' => $payment->id,
                'currency' => $payment->currency,
                'exchange_rate' => $payment->exchange_rate ?? 1,
                'created_by' => $userId,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            // DR Bank/Cash
            $jlBank = JournalLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $bank->gl_account_id,
                'description' => 'Bank Receipt ' . $payment->payment_no,
                'debit' => $payment->amount,
                'credit' => 0,
                'party_type' => 'NONE',
                'party_id' => null,
            ]);

            // CR AR (Customer)
            $jlAr = JournalLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $ar->id,
                'description' => 'AR Receipt ' . $payment->payment_no,
                'debit' => 0,
                'credit' => $payment->amount,
                'party_type' => 'CUSTOMER',
                'party_id' => $payment->party_id,
            ]);

            // GL entries
            $rate = (float)($payment->exchange_rate ?? 1);

            GLEntry::create([
                'company_id'=>$companyId,'posting_date'=>$payment->posting_date,
                'account_id'=>$jlBank->account_id,'journal_entry_id'=>$je->id,'journal_line_id'=>$jlBank->id,
                'debit'=>$payment->amount,'credit'=>0,'currency'=>$payment->currency,
                'amount_base'=>((float)$payment->amount)*$rate,'party_type'=>'NONE','party_id'=>null
            ]);

            GLEntry::create([
                'company_id'=>$companyId,'posting_date'=>$payment->posting_date,
                'account_id'=>$jlAr->account_id,'journal_entry_id'=>$je->id,'journal_line_id'=>$jlAr->id,
                'debit'=>0,'credit'=>$payment->amount,'currency'=>$payment->currency,
                'amount_base'=>(0-(float)$payment->amount)*$rate,'party_type'=>'CUSTOMER','party_id'=>$payment->party_id
            ]);

            // Optional: enforce allocations not exceed payment (strict control)
            if ($allocTotal > (float)$payment->amount + 0.0001) {
                throw new \RuntimeException('Allocated total cannot exceed receipt amount.');
            }

            return $payment;
        });
    }
}
