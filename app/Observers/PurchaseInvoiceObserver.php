<?php

namespace App\Observers;

use App\Services\Inventory\InventoryPostingService;

class PurchaseInvoiceObserver
{
    public function updated($invoice): void
    {
        if ($invoice->isDirty('status') && $invoice->status === 'SUBMITTED') {
            app(InventoryPostingService::class)->postPurchaseInvoice($invoice->load('lines'));
        }

        if ($invoice->isDirty('status') && $invoice->status === 'CANCELLED') {
            app(InventoryPostingService::class)->reverseVoucher('PurchaseInvoice', $invoice->id, $invoice->company_id);
        }
    }
}
