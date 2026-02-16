@extends('layouts.app')

@section('page_title','Payment Entries')

@section('content')
<div class="space-y-4 h-full flex flex-col">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold">Payment Entries</h1>
            <p class="text-xs text-slate-300">Receive customer payments and pay suppliers with allocations to invoices.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('modules.accounting.payments.create', ['type' => 'RECEIVE']) }}"
               class="px-3 py-2 rounded-lg bg-indigo-500/20 ring-1 ring-indigo-400/30 hover:bg-indigo-500/25 text-sm">
                + Receive Payment
            </a>
            <a href="{{ route('modules.accounting.payments.create', ['type' => 'PAY']) }}"
               class="px-3 py-2 rounded-lg bg-emerald-500/20 ring-1 ring-emerald-400/30 hover:bg-emerald-500/25 text-sm">
                + Pay Supplier
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="rounded-xl ring-1 ring-white/10 bg-black/10 p-3">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div>
                <label class="text-xs text-slate-300">Type</label>
                <select name="type" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10">
                    <option value="">All</option>
                    <option value="RECEIVE" @selected(request('type')==='RECEIVE')>Receive</option>
                    <option value="PAY" @selected(request('type')==='PAY')>Pay</option>
                </select>
            </div>

            <div>
                <label class="text-xs text-slate-300">Party</label>
                <input name="q" value="{{ request('q') }}"
                       placeholder="Customer/Supplier name, ref, no..."
                       class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 placeholder:text-slate-400 ring-1 ring-white/10" />
            </div>

            <div>
                <label class="text-xs text-slate-300">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10" />
            </div>

            <div>
                <label class="text-xs text-slate-300">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10" />
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button class="px-3 py-2 rounded-lg bg-white/10 ring-1 ring-white/10 hover:bg-white/15 text-sm">
                    Apply
                </button>
                <a href="{{ route('modules.accounting.payments.index') }}"
                   class="px-3 py-2 rounded-lg bg-white/5 ring-1 ring-white/10 hover:bg-white/10 text-sm">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="flex-1 min-h-0 rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
        <div class="overflow-auto h-full">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-black/40 backdrop-blur ring-1 ring-white/5">
                    <tr class="text-slate-200">
                        <th class="p-3 text-left">No</th>
                        <th class="p-3 text-left">Posting Date</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Party</th>
                        <th class="p-3 text-left">Account</th>
                        <th class="p-3 text-right">Amount</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                @forelse($payments as $p)
                    <tr class="hover:bg-white/5">
                        <td class="p-3 font-medium">
                            <a class="underline decoration-white/20 hover:decoration-white/40"
                               href="{{ route('modules.accounting.payments.show', $p) }}">
                                {{ $p->payment_no ?? ('PAY-' . $p->id) }}
                            </a>
                        </td>
                        <td class="p-3">{{ optional($p->posting_date)->format('Y-m-d') }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded bg-white/10 ring-1 ring-white/10 text-xs">
                                {{ $p->payment_type }}
                            </span>
                        </td>
                        <td class="p-3">
                            {{ $p->party_name ?? ($p->customer->name ?? $p->supplier->name ?? '—') }}
                        </td>
                        <td class="p-3">{{ $p->paymentAccount->name ?? '—' }}</td>
                        <td class="p-3 text-right font-semibold">{{ number_format((float)$p->amount,2) }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded bg-white/10 ring-1 ring-white/10 text-xs">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-2">
                                <a class="px-3 py-1.5 rounded-lg bg-white/10 ring-1 ring-white/10 hover:bg-white/15"
                                   href="{{ route('modules.accounting.payments.show', $p) }}">
                                    View
                                </a>
                                @if($p->status === 'DRAFT')
                                    <a class="px-3 py-1.5 rounded-lg bg-white/10 ring-1 ring-white/10 hover:bg-white/15"
                                       href="{{ route('modules.accounting.payments.edit', $p) }}">
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-6 text-center text-slate-400">No payments found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $payments->links() }}
    </div>

</div>
@endsection
