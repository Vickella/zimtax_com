<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StorePaymentReceiptRequest;
use App\Models\{Payment, Customer, BankAccount, SalesInvoice};
use App\Services\Sales\PaymentPostingService;

class PaymentReceiptController extends Controller
{
    public function index()
    {
        $receipts = Payment::query()
            ->where('company_id', company_id())
            ->where('payment_type','RECEIPT')
            ->orderByDesc('posting_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('modules.sales.receipts.index', compact('receipts'));
    }

    public function create()
    {
        $companyId = company_id();
        $customers = Customer::query()->where('company_id',$companyId)->where('is_active',1)->orderBy('name')->get();
        $banks = BankAccount::query()->where('company_id',$companyId)->where('is_active',1)->orderBy('name')->get();

        // optional: show open invoices list
        $openInvoices = SalesInvoice::query()
            ->where('company_id',$companyId)->where('status','SUBMITTED')
            ->orderByDesc('posting_date')->limit(50)->get();

        return view('modules.sales.receipts.create', compact('customers','banks','openInvoices'));
    }

    public function store(StorePaymentReceiptRequest $request, PaymentPostingService $svc)
    {
        $companyId = company_id();

        $payment = $svc->createReceipt($request->validated(), $companyId, (int)auth()->id());

        return redirect()->route('sales.receipts.show', $payment)->with('ok','Receipt created and posted.');
    }

    public function show(Payment $payment)
    {
        abort_unless($payment->company_id === company_id(), 404);
        $payment->load('allocations');
        return view('modules.sales.receipts.show', compact('payment'));
    }
}
