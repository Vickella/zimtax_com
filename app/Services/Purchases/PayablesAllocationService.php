<?php

// app/Services/Purchases/PayablesAllocationService.php
namespace App\Services\Purchases;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;

class PayablesAllocationService
{
    public function allocateToPurchaseInvoices(int $companyId, int $paymentId, array $allocations): void
    {
        $payment = Payment::query()
            ->forCompany($companyId)
            ->with('allocations')
            ->findOrFail($paymentId);

        if ($payment->status !== 'SUBMITTED') {
            abort(409, 'Payment must be SUBMITTED before allocation.');
        }
        if ($payment->party_type !== 'SUPPLIER') {
            abort(422, 'Payment party_type must be SUPPLIER for AP allocations.');
        }

        DB::transaction(function () use ($companyId, $payment, $allocations) {

            // lock payment row
            Payment::query()->where('id', $payment->id)->lockForUpdate()->first();

            // Current total allocated
            $alreadyAllocated = PaymentAllocation::query()
                ->where('payment_id', $payment->id)
                ->sum('allocated_amount');

            $remaining = bcsub((string)$payment->amount, (string)$alreadyAllocated, 2);

            foreach ($allocations as $row) {
                $piId = (int)$row['purchase_invoice_id'];
                $amt = (string)$row['amount'];

                if (bccomp($amt, '0.00', 2) <= 0) continue;

                $invoice = PurchaseInvoice::query()
                    ->forCompany($companyId)
                    ->where('supplier_id', $payment->party_id)
                    ->where('status', 'SUBMITTED')
                    ->findOrFail($piId);

                // calculate invoice outstanding = total - allocated (across all payments)
                $invoiceAllocated = PaymentAllocation::query()
                    ->where('reference_type', 'PURCHASE_INVOICE')
                    ->where('reference_id', $invoice->id)
                    ->whereHas('payment', function ($q) use ($companyId) {
                        $q->where('company_id', $companyId)->where('status', 'SUBMITTED');
                    })
                    ->sum('allocated_amount');

                $outstanding = bcsub((string)$invoice->total, (string)$invoiceAllocated, 2);

                if (bccomp($outstanding, '0.00', 2) <= 0) {
                    continue; // already fully settled
                }

                // clamp allocation to both remaining payment and invoice outstanding
                $alloc = $amt;
                if (bccomp($alloc, $remaining, 2) === 1) $alloc = $remaining;
                if (bccomp($alloc, $outstanding, 2) === 1) $alloc = $outstanding;

                if (bccomp($alloc, '0.00', 2) <= 0) continue;

                PaymentAllocation::query()->create([
                    'payment_id' => $payment->id,
                    'reference_type' => 'PURCHASE_INVOICE',
                    'reference_id' => $invoice->id,
                    'allocated_amount' => $alloc,
                ]);

                $remaining = bcsub($remaining, $alloc, 2);
                if (bccomp($remaining, '0.00', 2) <= 0) break;
            }
        });
    }
}
