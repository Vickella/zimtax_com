<?php

namespace App\Observers;

use App\Services\Inventory\InventoryPostingService;

class SalesInvoiceObserver
{
    public function updated($invoice): void
    {
        // When status changes to SUBMITTED, post stock out
        if ($invoice->isDirty('status') && $invoice->status === 'SUBMITTED') {
            app(InventoryPostingService::class)->postSalesInvoice($invoice->load('lines'));
        }

        // When status changes to CANCELLED, reverse
        if ($invoice->isDirty('status') && $invoice->status === 'CANCELLED') {
            app(InventoryPostingService::class)->reverseVoucher('SalesInvoice', $invoice->id, $invoice->company_id);
        }
    }
}
