<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Services\Accounting\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $type = $request->get('type');
        $status = $request->get('status');
        $q = trim((string)$request->get('q', ''));

        $payments = Payment::query()
            ->with(['customer', 'supplier', 'paymentAccount'])
            ->where('company_id', company_id())
            ->when($type, fn($x) => $x->where('payment_type', $type))
            ->when($status, fn($x) => $x->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('payment_no', 'like', "%{$q}%")
                       ->orWhere('reference_no', 'like', "%{$q}%")
                       ->orWhere('remarks', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('posting_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('modules.accounting.payments.index', compact('payments', 'type', 'status', 'q'));
    }

    public function create()
    {
        $payment = new Payment([
            'posting_date' => now()->toDateString(),
            'currency' => company_currency(),
            'exchange_rate' => 1,
            'status' => 'DRAFT',
            'payment_type' => request('type', 'RECEIVE'),
        ]);

        $accounts = ChartOfAccount::query()
            ->where('company_id', company_id())
            ->where('is_active', 1)
            ->whereIn('type', ['ASSET']) // Bank/Cash accounts
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        $customers = Customer::query()
            ->where('company_id', company_id())
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        $suppliers = Supplier::query()
            ->where('company_id', company_id())
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get open invoices with outstanding amounts
        $openSalesInvoices = SalesInvoice::query()
            ->where('company_id', company_id())
            ->where('status', 'SUBMITTED')
            ->whereRaw('total > COALESCE(paid_amount, 0)')
            ->orderBy('due_date')
            ->get(['id', 'invoice_no', 'total', DB::raw('total - COALESCE(paid_amount, 0) as outstanding')]);

        $openPurchaseInvoices = PurchaseInvoice::query()
            ->where('company_id', company_id())
            ->where('status', 'SUBMITTED')
            ->whereRaw('total > COALESCE(paid_amount, 0)')
            ->orderBy('due_date')
            ->get(['id', 'invoice_no', 'total', DB::raw('total - COALESCE(paid_amount, 0) as outstanding')]);

        return view('modules.accounting.payments.create', compact(
            'payment',
            'accounts',
            'customers',
            'suppliers',
            'openSalesInvoices',
            'openPurchaseInvoices'
        ));
    }

    public function store(Request $request)
{
    // THIS WILL ALWAYS SHOW IF THE METHOD IS CALLED
    file_put_contents(storage_path('logs/debug.txt'), 'Store method called at ' . now() . "\n", FILE_APPEND);
    file_put_contents(storage_path('logs/debug.txt'), 'Action: ' . $request->input('action') . "\n", FILE_APPEND);
    file_put_contents(storage_path('logs/debug.txt'), 'Method: ' . $request->method() . "\n", FILE_APPEND);
    file_put_contents(storage_path('logs/debug.txt'), 'URL: ' . $request->fullUrl() . "\n", FILE_APPEND);
    file_put_contents(storage_path('logs/debug.txt'), 'Data: ' . json_encode($request->except('_token')) . "\n\n", FILE_APPEND); 
    
    // DEBUG: Log the incoming request
    Log::info('PAYMENT STORE METHOD CALLED', [
        'action' => $request->input('action'),
        'payment_type' => $request->input('payment_type'),
        'amount' => $request->input('amount'),
        'all_data' => $request->except('_token')
    ]);

    // TEMPORARY: Dump and die to see if we get here
    // dd('Store method hit!', $request->all());

    $validated = $request->validate([
        'payment_type' => 'required|in:RECEIVE,PAY',
        'posting_date' => 'required|date',
        'payment_account_id' => 'required|exists:chart_of_accounts,id',
        'currency' => 'required|string|size:3',
        'exchange_rate' => 'required|numeric|min:0',
        'amount' => 'required|numeric|min:0.01',
        'reference_no' => 'nullable|string|max:50',
        'reference_date' => 'nullable|date',
        'remarks' => 'nullable|string|max:255',
        'customer_id' => 'required_if:payment_type,RECEIVE|nullable|exists:customers,id',
        'supplier_id' => 'required_if:payment_type,PAY|nullable|exists:suppliers,id',
        'allocations' => 'required|array|min:1',
        'allocations.*.invoice_key' => 'required|string',
        'allocations.*.allocated_amount' => 'required|numeric|min:0.01',
        'action' => 'required|in:draft,submit',
    ]);

    // Parse invoice key to get type and ID
    $allocations = [];
    foreach ($validated['allocations'] as $alloc) {
        $parts = explode(':', $alloc['invoice_key']);
        $allocations[] = [
            'invoice_type' => $parts[0] === 'SI' ? 'SalesInvoice' : 'PurchaseInvoice',
            'invoice_id' => $parts[1],
            'allocated_amount' => $alloc['allocated_amount'],
        ];
    }

    // Verify total allocated equals payment amount
    $totalAllocated = array_sum(array_column($allocations, 'allocated_amount'));
    if (round($totalAllocated, 2) !== round($validated['amount'], 2)) {
        Log::warning('Allocation mismatch', [
            'payment_amount' => $validated['amount'],
            'total_allocated' => $totalAllocated
        ]);
        return back()->withErrors(['allocations' => 'Total allocated amount must equal payment amount.'])->withInput();
    }

    $data = array_merge($validated, [
        'company_id' => company_id(),
        'allocations' => $allocations,
    ]);

    try {
        Log::info('Creating payment with data', $data);
        
        // Create the payment
        $payment = $this->paymentService->create($data, auth()->id());
        
        Log::info('Payment created', ['payment_id' => $payment->id, 'payment_no' => $payment->payment_no]);
        
        // If action is submit, also submit it
        if ($request->input('action') === 'submit') {
            Log::info('Submitting payment', ['payment_id' => $payment->id]);
            
            $payment = $this->paymentService->submit($payment, auth()->id());
            
            Log::info('Payment submitted successfully', ['payment_id' => $payment->id]);
            
            return redirect()->route('modules.accounting.payments.show', $payment)
                ->with('success', 'Payment submitted and posted to GL successfully.');
        }
        
        return redirect()->route('modules.accounting.payments.show', $payment)
            ->with('success', 'Payment draft saved successfully.');
            
    } catch (\Exception $e) {
        Log::error('Payment creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->withErrors(['error' => 'Failed to save payment: ' . $e->getMessage()])->withInput();
    }
}

    public function show(Payment $payment)
    {
        abort_unless($payment->company_id === company_id(), 403);
        
        $payment->load(['paymentAccount', 'customer', 'supplier', 'journalEntry', 'allocations']);
        
        return view('modules.accounting.payments.show', compact('payment'));
    }

    public function submit(Payment $payment)
    {
        abort_unless($payment->company_id === company_id(), 403);

        try {
            $payment = $this->paymentService->submit($payment, auth()->id());
            
            return redirect()->route('modules.accounting.payments.show', $payment)
                ->with('success', 'Payment submitted and posted to GL successfully.');
        } catch (\Exception $e) {
            Log::error('Payment submission failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to submit payment: ' . $e->getMessage()]);
        }
    }

    public function reverse(Payment $payment, Request $request)
    {
        abort_unless($payment->company_id === company_id(), 403);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $payment = $this->paymentService->reverse($payment, auth()->id(), $validated['reason'] ?? null);
            
            return redirect()->route('modules.accounting.payments.show', $payment)
                ->with('success', 'Payment reversed successfully.');
        } catch (\Exception $e) {
            Log::error('Payment reversal failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to reverse payment: ' . $e->getMessage()]);
        }
    }

    public function cancel(Payment $payment)
    {
        abort_unless($payment->company_id === company_id(), 403);

        if ($payment->status !== 'DRAFT') {
            return back()->withErrors(['error' => 'Only draft payments can be cancelled.']);
        }

        $payment->delete();

        return redirect()->route('modules.accounting.payments.index')
            ->with('success', 'Payment cancelled successfully.');
    }
}