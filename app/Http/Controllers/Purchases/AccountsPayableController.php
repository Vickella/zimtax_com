<?php

// app/Http/Controllers/Purchases/AccountsPayableController.php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;

class AccountsPayableController extends Controller
{
    // app/Http/Controllers/Purchases/AccountsPayableController.php (REPLACE aging() method)
public function aging()
{
    $companyId = company_id();

    // Outstanding = PI.total - SUM(allocations on submitted payments)
    $rows = \App\Models\PurchaseInvoice::query()
        ->forCompany($companyId)
        ->where('status', 'SUBMITTED')
        ->selectRaw('supplier_id,
            SUM(total) as total_invoiced,
            SUM(
                GREATEST(
                    total - (
                        SELECT COALESCE(SUM(pa.allocated_amount),0)
                        FROM payment_allocations pa
                        JOIN payments p ON p.id = pa.payment_id
                        WHERE pa.reference_type = "PURCHASE_INVOICE"
                          AND pa.reference_id = purchase_invoices.id
                          AND p.company_id = ?
                          AND p.status = "SUBMITTED"
                    ), 0
                )
            ) as total_outstanding,
            SUM(CASE WHEN due_date IS NULL OR due_date >= CURDATE() THEN
                GREATEST(
                    total - (
                        SELECT COALESCE(SUM(pa.allocated_amount),0)
                        FROM payment_allocations pa
                        JOIN payments p ON p.id = pa.payment_id
                        WHERE pa.reference_type = "PURCHASE_INVOICE"
                          AND pa.reference_id = purchase_invoices.id
                          AND p.company_id = ?
                          AND p.status = "SUBMITTED"
                    ), 0
                )
            ELSE 0 END) as current_bucket,
            SUM(CASE WHEN due_date < CURDATE() AND DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 30 THEN
                GREATEST(
                    total - (
                        SELECT COALESCE(SUM(pa.allocated_amount),0)
                        FROM payment_allocations pa
                        JOIN payments p ON p.id = pa.payment_id
                        WHERE pa.reference_type = "PURCHASE_INVOICE"
                          AND pa.reference_id = purchase_invoices.id
                          AND p.company_id = ?
                          AND p.status = "SUBMITTED"
                    ), 0
                )
            ELSE 0 END) as bucket_1_30,
            SUM(CASE WHEN due_date < CURDATE() AND DATEDIFF(CURDATE(), due_date) BETWEEN 31 AND 60 THEN
                GREATEST(
                    total - (
                        SELECT COALESCE(SUM(pa.allocated_amount),0)
                        FROM payment_allocations pa
                        JOIN payments p ON p.id = pa.payment_id
                        WHERE pa.reference_type = "PURCHASE_INVOICE"
                          AND pa.reference_id = purchase_invoices.id
                          AND p.company_id = ?
                          AND p.status = "SUBMITTED"
                    ), 0
                )
            ELSE 0 END) as bucket_31_60,
            SUM(CASE WHEN due_date < CURDATE() AND DATEDIFF(CURDATE(), due_date) BETWEEN 61 AND 90 THEN
                GREATEST(
                    total - (
                        SELECT COALESCE(SUM(pa.allocated_amount),0)
                        FROM payment_allocations pa
                        JOIN payments p ON p.id = pa.payment_id
                        WHERE pa.reference_type = "PURCHASE_INVOICE"
                          AND pa.reference_id = purchase_invoices.id
                          AND p.company_id = ?
                          AND p.status = "SUBMITTED"
                    ), 0
                )
            ELSE 0 END) as bucket_61_90,
            SUM(CASE WHEN due_date < CURDATE() AND DATEDIFF(CURDATE(), due_date) > 90 THEN
                GREATEST(
                    total - (
                        SELECT COALESCE(SUM(pa.allocated_amount),0)
                        FROM payment_allocations pa
                        JOIN payments p ON p.id = pa.payment_id
                        WHERE pa.reference_type = "PURCHASE_INVOICE"
                          AND pa.reference_id = purchase_invoices.id
                          AND p.company_id = ?
                          AND p.status = "SUBMITTED"
                    ), 0
                )
            ELSE 0 END) as bucket_90_plus
        ', [$companyId,$companyId,$companyId,$companyId,$companyId,$companyId])
        ->with('supplier:id,name')
        ->groupBy('supplier_id')
        ->orderBy('supplier_id')
        ->get();

    return view('modules.purchases.ap.aging', compact('rows'));
}

}
