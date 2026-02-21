<?php

namespace App\Services\Accounting;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\GLEntry;
use App\Models\ChartOfAccount;
use App\Services\Numbers\NumberSeries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $journalPostingService;

    public function __construct(JournalPostingService $journalPostingService)
    {
        $this->journalPostingService = $journalPostingService;
    }

    /**
     * Create a new payment (DRAFT)
     */
    public function create(array $data, int $userId): Payment
    {
        return DB::transaction(function () use ($data, $userId) {
            // Generate payment number
            $paymentNo = $this->generatePaymentNumber($data['company_id']);
            
            Log::info('Creating payment', [
                'payment_no' => $paymentNo,
                'type' => $data['payment_type'],
                'amount' => $data['amount']
            ]);
            
            $payment = Payment::create([
                'company_id' => $data['company_id'],
                'payment_no' => $paymentNo,
                'payment_type' => $data['payment_type'],
                'posting_date' => $data['posting_date'],
                'payment_account_id' => $data['payment_account_id'],
                'currency' => $data['currency'],
                'exchange_rate' => $data['exchange_rate'],
                'amount' => $data['amount'],
                'reference_no' => $data['reference_no'] ?? null,
                'reference_date' => $data['reference_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'status' => 'DRAFT',
                'created_by' => $userId,
            ]);

            // Create allocations
            if (!empty($data['allocations'])) {
                foreach ($data['allocations'] as $alloc) {
                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'invoice_type' => $alloc['invoice_type'],
                        'invoice_id' => $alloc['invoice_id'],
                        'allocated_amount' => $alloc['allocated_amount'],
                    ]);
                    
                    Log::info('Created allocation', [
                        'payment_id' => $payment->id,
                        'invoice_type' => $alloc['invoice_type'],
                        'invoice_id' => $alloc['invoice_id'],
                        'amount' => $alloc['allocated_amount']
                    ]);
                }
            }

            return $payment;
        });
    }

    /**
     * Submit payment - creates journal entry and posts to GL
     */
    public function submit(Payment $payment, int $userId): Payment
    {
        return DB::transaction(function () use ($payment, $userId) {
            Log::info('Submitting payment', [
                'payment_id' => $payment->id,
                'payment_no' => $payment->payment_no,
                'amount' => $payment->amount,
                'type' => $payment->payment_type
            ]);

            if ($payment->status !== 'DRAFT') {
                throw new \Exception('Only draft payments can be submitted. Current status: ' . $payment->status);
            }

            // Verify payment is balanced with allocations
            $totalAllocated = $payment->allocations()->sum('allocated_amount');
            if (round($totalAllocated, 2) !== round($payment->amount, 2)) {
                throw new \Exception(
                    "Payment amount ({$payment->amount}) does not match total allocated ({$totalAllocated}). " .
                    "All allocations must total the payment amount."
                );
            }

            // Get account mappings
            $accountMap = $this->getAccountMap($payment->company_id);
            
            // Build journal lines based on payment type
            $lines = $this->buildJournalLines($payment, $accountMap);
            
            Log::info('Journal lines built', [
                'lines' => $lines
            ]);

            // Create and post journal entry
            $journalEntry = $this->journalPostingService->createPostedJournalWithLines(
                $payment->company_id,
                $payment->posting_date,
                $this->getJournalMemo($payment),
                'Payment',
                $payment->id,
                $payment->currency,
                $payment->exchange_rate,
                $userId,
                $lines
            );

            Log::info('Journal entry created', [
                'journal_entry_id' => $journalEntry->id,
                'entry_no' => $journalEntry->entry_no
            ]);

            // Update payment status
            $payment->update([
                'status' => 'SUBMITTED',
                'submitted_by' => $userId,
                'submitted_at' => now(),
                'journal_entry_id' => $journalEntry->id,
            ]);

            // Update invoice paid amounts
            $this->updateInvoicePaidAmounts($payment);

            Log::info('Payment submitted successfully', [
                'payment_no' => $payment->payment_no,
                'journal_entry' => $journalEntry->entry_no,
                'amount' => $payment->amount
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Build journal lines based on payment type
     */
    protected function buildJournalLines(Payment $payment, array $accountMap): array
    {
        $lines = [];

        if ($payment->payment_type === 'RECEIVE') {
            // Customer Payment: Debit Cash/Bank, Credit Accounts Receivable
            $lines[] = [
                'account_id' => $payment->payment_account_id,
                'description' => "Customer payment received - {$payment->payment_no}",
                'debit' => $payment->amount,
                'credit' => 0,
                'party_type' => 'CUSTOMER',
                'party_id' => $payment->customer_id,
            ];

            $lines[] = [
                'account_id' => $accountMap['accounts_receivable'],
                'description' => "Customer payment allocation - {$payment->payment_no}",
                'debit' => 0,
                'credit' => $payment->amount,
                'party_type' => 'CUSTOMER',
                'party_id' => $payment->customer_id,
            ];
        } else {
            // Supplier Payment: Debit Accounts Payable, Credit Cash/Bank
            $lines[] = [
                'account_id' => $accountMap['accounts_payable'],
                'description' => "Supplier payment - {$payment->payment_no}",
                'debit' => $payment->amount,
                'credit' => 0,
                'party_type' => 'SUPPLIER',
                'party_id' => $payment->supplier_id,
            ];

            $lines[] = [
                'account_id' => $payment->payment_account_id,
                'description' => "Supplier payment - {$payment->payment_no}",
                'debit' => 0,
                'credit' => $payment->amount,
                'party_type' => 'SUPPLIER',
                'party_id' => $payment->supplier_id,
            ];
        }

        return $lines;
    }

    /**
     * Update invoice paid amounts after payment submission
     */
    protected function updateInvoicePaidAmounts(Payment $payment): void
    {
        foreach ($payment->allocations as $alloc) {
            if ($alloc->invoice_type === 'SalesInvoice') {
                // Update sales invoice paid amount
                DB::table('sales_invoices')
                    ->where('id', $alloc->invoice_id)
                    ->increment('paid_amount', $alloc->allocated_amount);
                    
                Log::info('Updated sales invoice paid amount', [
                    'invoice_id' => $alloc->invoice_id,
                    'amount' => $alloc->allocated_amount
                ]);
                
            } elseif ($alloc->invoice_type === 'PurchaseInvoice') {
                // Update purchase invoice paid amount
                DB::table('purchase_invoices')
                    ->where('id', $alloc->invoice_id)
                    ->increment('paid_amount', $alloc->allocated_amount);
                    
                Log::info('Updated purchase invoice paid amount', [
                    'invoice_id' => $alloc->invoice_id,
                    'amount' => $alloc->allocated_amount
                ]);
            }
        }
    }

    /**
     * Reverse a payment (void with reversing entry)
     */
    public function reverse(Payment $payment, int $userId, string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $userId, $reason) {
            Log::info('Reversing payment', [
                'payment_id' => $payment->id,
                'payment_no' => $payment->payment_no,
                'reason' => $reason
            ]);

            if ($payment->status !== 'SUBMITTED') {
                throw new \Exception('Only submitted payments can be reversed. Current status: ' . $payment->status);
            }

            // Get account mappings
            $accountMap = $this->getAccountMap($payment->company_id);
            
            // Build reversing journal lines
            $lines = $this->buildReverseJournalLines($payment, $accountMap);

            // Create reversing journal entry
            $journalEntry = $this->journalPostingService->createPostedJournalWithLines(
                $payment->company_id,
                now()->toDateString(),
                "Reversal: {$payment->payment_no} - " . ($reason ?? 'Void payment'),
                'PaymentReversal',
                $payment->id,
                $payment->currency,
                $payment->exchange_rate,
                $userId,
                $lines
            );

            // Reverse invoice paid amounts
            foreach ($payment->allocations as $alloc) {
                if ($alloc->invoice_type === 'SalesInvoice') {
                    DB::table('sales_invoices')
                        ->where('id', $alloc->invoice_id)
                        ->decrement('paid_amount', $alloc->allocated_amount);
                } elseif ($alloc->invoice_type === 'PurchaseInvoice') {
                    DB::table('purchase_invoices')
                        ->where('id', $alloc->invoice_id)
                        ->decrement('paid_amount', $alloc->allocated_amount);
                }
            }

            // Update payment status
            $payment->update([
                'status' => 'REVERSED',
                'reversed_by' => $userId,
                'reversed_at' => now(),
                'reversal_reason' => $reason,
                'reversal_journal_entry_id' => $journalEntry->id,
            ]);

            Log::info('Payment reversed successfully', [
                'payment_no' => $payment->payment_no,
                'reversal_journal' => $journalEntry->entry_no
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Build reversing journal lines
     */
    protected function buildReverseJournalLines(Payment $payment, array $accountMap): array
    {
        $lines = [];

        if ($payment->payment_type === 'RECEIVE') {
            // Reverse customer payment: Debit Accounts Receivable, Credit Cash/Bank
            $lines[] = [
                'account_id' => $accountMap['accounts_receivable'],
                'description' => "Reversal: {$payment->payment_no}",
                'debit' => $payment->amount,
                'credit' => 0,
                'party_type' => 'CUSTOMER',
                'party_id' => $payment->customer_id,
            ];

            $lines[] = [
                'account_id' => $payment->payment_account_id,
                'description' => "Reversal: {$payment->payment_no}",
                'debit' => 0,
                'credit' => $payment->amount,
                'party_type' => 'CUSTOMER',
                'party_id' => $payment->customer_id,
            ];
        } else {
            // Reverse supplier payment: Debit Cash/Bank, Credit Accounts Payable
            $lines[] = [
                'account_id' => $payment->payment_account_id,
                'description' => "Reversal: {$payment->payment_no}",
                'debit' => $payment->amount,
                'credit' => 0,
                'party_type' => 'SUPPLIER',
                'party_id' => $payment->supplier_id,
            ];

            $lines[] = [
                'account_id' => $accountMap['accounts_payable'],
                'description' => "Reversal: {$payment->payment_no}",
                'debit' => 0,
                'credit' => $payment->amount,
                'party_type' => 'SUPPLIER',
                'party_id' => $payment->supplier_id,
            ];
        }

        return $lines;
    }

    /**
     * Get account mappings from configuration or database
     */
    protected function getAccountMap(int $companyId): array
    {
        // Try to get from settings table first, fallback to defaults
        $arAccount = ChartOfAccount::where('company_id', $companyId)
            ->where('code', '1100') // Accounts Receivable
            ->orWhere('name', 'like', '%Receivable%')
            ->where('type', 'ASSET')
            ->first();
            
        $apAccount = ChartOfAccount::where('company_id', $companyId)
            ->where('code', '2100') // Accounts Payable
            ->orWhere('name', 'like', '%Payable%')
            ->where('type', 'LIABILITY')
            ->first();

        if (!$arAccount) {
            throw new \Exception('Accounts Receivable account not found. Please set up your chart of accounts.');
        }
        
        if (!$apAccount) {
            throw new \Exception('Accounts Payable account not found. Please set up your chart of accounts.');
        }

        return [
            'accounts_receivable' => $arAccount->id,
            'accounts_payable' => $apAccount->id,
        ];
    }

    /**
     * Generate payment number
     */
    protected function generatePaymentNumber(int $companyId): string
    {
        $prefix = 'PMT';
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastPayment = Payment::where('company_id', $companyId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPayment) {
            $lastNumber = intval(substr($lastPayment->payment_no, -4));
            $sequence = $lastNumber + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    /**
     * Generate journal memo
     */
    protected function getJournalMemo(Payment $payment): string
    {
        if ($payment->payment_type === 'RECEIVE') {
            $customer = $payment->customer;
            $partyName = $customer ? $customer->name : "Customer #{$payment->customer_id}";
            return "Customer payment {$payment->payment_no} from {$partyName}";
        } else {
            $supplier = $payment->supplier;
            $partyName = $supplier ? $supplier->name : "Supplier #{$payment->supplier_id}";
            return "Supplier payment {$payment->payment_no} to {$partyName}";
        }
    }
}