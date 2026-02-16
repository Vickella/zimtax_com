<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;

class PurchasesModuleController extends Controller
{
    public function index()
    {
        $companyId = company_id();

        $stats = [
            'suppliers' => Supplier::query()->forCompany($companyId)->where('is_active', 1)->count(),
            'purchase_invoices' => PurchaseInvoice::query()->forCompany($companyId)->count(),
            'purchase_invoices_draft' => PurchaseInvoice::query()->forCompany($companyId)->where('status', 'DRAFT')->count(),
        ];

        return view('modules.purchases.index', compact('stats'));
    }
}
