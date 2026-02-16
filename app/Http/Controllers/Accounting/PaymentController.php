<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type'); // RECEIVE / PAY
        $status = $request->get('status');
        $q = trim((string)$request->get('q', ''));

        $payments = Payment::query()
            ->when($type, fn($x) => $x->where('payment_type', $type))
            ->when($status, fn($x) => $x->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('payment_no','like',"%{$q}%")
                       ->orWhere('reference','like',"%{$q}%")
                       ->orWhere('remarks','like',"%{$q}%");
                });
            })
            ->orderByDesc('posting_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('modules.accounting.payments.index', compact('payments','type','status','q'));
    }

    public function create()
    {
        $payment = new Payment([
            'posting_date' => now()->toDateString(),
            'currency' => company_currency(),
            'exchange_rate' => 1,
            'status' => 'DRAFT',
            'payment_type' => 'RECEIVE',
        ]);

        $accounts = ChartOfAccount::query()
            ->where('is_active', 1)
            ->orderBy('code')
            ->get(['id','code','name','type']);

        return view('modules.accounting.payments.create', compact('payment','accounts'));
    }

    public function store(Request $request)
    {
        abort(501, 'Use your PaymentService implementation here (you said you already have it).');
    }

    public function show(Payment $payment)
    {
        $payment->load(['fromAccount','toAccount','journalEntry']);
        return view('modules.accounting.payments.show', compact('payment'));
    }

    public function submit(Payment $payment)
    {
        abort(501, 'Use your PaymentService->submit() here.');
    }

    public function reverse(Payment $payment)
    {
        abort(501, 'Use your PaymentService->reverse() here.');
    }

    public function cancel(Payment $payment)
    {
        abort(501, 'Use your PaymentService->cancel() here.');
    }
}
