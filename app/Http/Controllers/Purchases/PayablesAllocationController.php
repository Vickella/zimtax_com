<?php


namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchases\AllocatePaymentToPurchaseInvoicesRequest;
use App\Models\Payment;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Services\Purchases\PayablesAllocationService;

class PayablesAllocationController extends Controller
{
    public function __construct(private PayablesAllocationService $service) {}

    public function create()
    {
        $companyId = company_id();

        $payments = Payment::query()
            ->forCompany($companyId)
            ->where('status', 'SUBMITTED')
            ->where('party_type', 'SUPPLIER')
            ->orderByDesc('posting_date')
            ->limit(100)
            ->get();

        $suppliers = Supplier::query()
            ->forCompany($companyId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('modules.purchases.ap.allocate', compact('payments','suppliers'));
    }

    public function supplierOpenInvoices(int $supplierId)
    {
        $companyId = company_id();

        $invoices = PurchaseInvoice::query()
            ->forCompany($companyId)
            ->where('supplier_id', $supplierId)
            ->where('status', 'SUBMITTED')
            ->orderBy('posting_date')
            ->get();

        return response()->json($invoices);
    }

    public function store(AllocatePaymentToPurchaseInvoicesRequest $request)
    {
        $companyId = company_id();
        $data = $request->validated();

        $this->service->allocateToPurchaseInvoices(
            $companyId,
            (int)$data['payment_id'],
            $data['allocations']
        );

        return redirect()->route('modules.purchases.ap.aging')->with('success', 'Allocations saved.');
    }
}
