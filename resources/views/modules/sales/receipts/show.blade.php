@extends('layouts.erp')
@section('page_title','Receipt')

@section('content')
<div class="h-full overflow-auto space-y-4">

    @if(session('ok'))
        <div class="p-3 rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20 text-emerald-200 text-sm">
            {{ session('ok') }}
        </div>
    @endif

    <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-semibold">{{ $payment->payment_no }}</div>
                <div class="text-sm text-slate-300">Status: {{ $payment->status }}</div>
            </div>
            <a href="{{ route('sales.receipts.index') }}"
               class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
                Back
            </a>
        </div>

        <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            <div>Posting Date: <span class="text-slate-200">{{ $payment->posting_date }}</span></div>
            <div>Currency: <span class="text-slate-200">{{ $payment->currency }}</span></div>
            <div>Amount: <span class="text-slate-200">{{ number_format((float)$payment->amount,2) }}</span></div>
            <div>Customer ID: <span class="text-slate-200">{{ $payment->party_id }}</span></div>
            <div>Bank Account ID: <span class="text-slate-200">{{ $payment->bank_account_id }}</span></div>
            <div>Reference: <span class="text-slate-200">{{ $payment->reference }}</span></div>
        </div>
    </div>

    <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-4 space-y-3">
        <div class="text-sm font-semibold">Allocations</div>

        <div class="rounded-xl ring-1 ring-white/10 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Reference ID</th>
                        <th class="p-3 text-left">Allocated Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($payment->allocations as $a)
                        <tr class="hover:bg-white/5">
                            <td class="p-3">{{ $a->reference_type }}</td>
                            <td class="p-3">
                                @if($a->reference_type === 'SALES_INVOICE')
                                    <a class="text-indigo-200 hover:underline"
                                       href="{{ route('sales.invoices.show',$a->reference_id) }}">
                                        {{ $a->reference_id }}
                                    </a>
                                @else
                                    {{ $a->reference_id }}
                                @endif
                            </td>
                            <td class="p-3">{{ number_format((float)$a->allocated_amount,2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="p-4 text-slate-300" colspan="3">No allocations recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="text-xs text-slate-400">
            Receipt posts: <span class="text-slate-200">DR Bank/Cash</span> and <span class="text-slate-200">CR Accounts Receivable</span>.
        </div>
    </div>

</div>
@endsection
