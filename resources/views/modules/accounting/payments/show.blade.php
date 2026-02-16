@extends('layouts.app')

@section('page_title','Payment Entry')

@section('content')
<div class="max-w-6xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold">{{ $payment->payment_no ?? ('PAY-' . $payment->id) }}</h1>
            <p class="text-xs text-slate-300">
                {{ $payment->payment_type }} • {{ optional($payment->posting_date)->format('Y-m-d') }} • Status: {{ $payment->status }}
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('modules.accounting.payments.index') }}"
               class="px-3 py-2 rounded-lg bg-white/10 ring-1 ring-white/10 hover:bg-white/15 text-sm">
                Back
            </a>

            @if($payment->status === 'DRAFT')
                <a href="{{ route('modules.accounting.payments.edit', $payment) }}"
                   class="px-3 py-2 rounded-lg bg-white/10 ring-1 ring-white/10 hover:bg-white/15 text-sm">
                    Edit
                </a>

                {{-- If your controller has submit route --}}
                <form method="POST" action="{{ route('modules.accounting.payments.submit', $payment) }}">
                    @csrf
                    <button class="px-3 py-2 rounded-lg bg-emerald-500/20 ring-1 ring-emerald-400/30 hover:bg-emerald-500/25 text-sm">
                        Submit & Post
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
            <div class="text-xs text-slate-400">Party</div>
            <div class="font-semibold">
                {{ $payment->party_name ?? ($payment->customer->name ?? $payment->supplier->name ?? '—') }}
            </div>
            <div class="text-xs text-slate-400 mt-2">Payment Account</div>
            <div class="text-sm">{{ $payment->paymentAccount->name ?? '—' }}</div>
        </div>

        <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
            <div class="text-xs text-slate-400">Currency</div>
            <div class="font-semibold">{{ $payment->currency }}</div>
            <div class="text-xs text-slate-400 mt-2">Exchange Rate</div>
            <div class="text-sm">{{ number_format((float)$payment->exchange_rate, 6) }}</div>
        </div>

        <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
            <div class="text-xs text-slate-400">Amount</div>
            <div class="text-xl font-bold">{{ number_format((float)$payment->amount, 2) }}</div>
            <div class="text-xs text-slate-400 mt-2">Reference</div>
            <div class="text-sm">{{ $payment->reference_no ?? '—' }}</div>
        </div>
    </div>

    {{-- Allocations --}}
    <div class="rounded-xl ring-1 ring-white/10 bg-black/10 overflow-hidden">
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div class="font-semibold">Allocations</div>
            <div class="text-xs text-slate-400">Applied to invoices</div>
        </div>

        <div class="overflow-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="p-3 text-left">Invoice</th>
                        <th class="p-3 text-right">Invoice Total</th>
                        <th class="p-3 text-right">Outstanding</th>
                        <th class="p-3 text-right">Allocated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                @forelse($payment->allocations ?? [] as $a)
                    <tr>
                        <td class="p-3">
                            {{ $a->invoice_no ?? $a['invoice_no'] ?? ('#' . ($a->invoice_id ?? $a['invoice_id'] ?? '')) }}
                            <div class="text-xs text-slate-400">{{ $a->invoice_type ?? $a['invoice_type'] ?? '' }}</div>
                        </td>
                        <td class="p-3 text-right">{{ number_format((float)($a->invoice_total ?? $a['invoice_total'] ?? 0),2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)($a->outstanding ?? $a['outstanding'] ?? 0),2) }}</td>
                        <td class="p-3 text-right font-semibold">{{ number_format((float)($a->allocated_amount ?? $a['allocated_amount'] ?? 0),2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-400">No allocations.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
