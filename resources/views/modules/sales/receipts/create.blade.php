@extends('layouts.erp')
@section('page_title','New Receipt')

@section('content')
<div class="h-full overflow-auto space-y-4">

    @if($errors->any())
        <div class="p-3 rounded-lg bg-red-500/10 ring-1 ring-red-500/20 text-red-200 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('modules.sales.receipts.store') }}" class="space-y-4">
        @csrf

        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs text-slate-300">Customer</label>
                    <select name="customer_id" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" required>
                        <option value="">Select...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-slate-300">Bank / Cash Account</label>
                    <select name="bank_account_id" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" required>
                        <option value="">Select...</option>
                        @foreach($banks as $b)
                            <option value="{{ $b->id }}" @selected(old('bank_account_id') == $b->id)>
                                {{ $b->name }} ({{ $b->currency }})
                            </option>
                        @endforeach
                    </select>
                    <div class="text-[11px] text-slate-400 mt-1">This posts DR to the linked GL account.</div>
                </div>

                <div>
                    <label class="text-xs text-slate-300">Posting Date</label>
                    <input type="date" name="posting_date"
                           value="{{ old('posting_date', now()->toDateString()) }}"
                           class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" required>
                </div>

                <div>
                    <label class="text-xs text-slate-300">Currency</label>
                    <input name="currency" value="{{ old('currency', company_currency()) }}"
                           class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" required>
                </div>

                <div>
                    <label class="text-xs text-slate-300">Exchange Rate</label>
                    <input name="exchange_rate" value="{{ old('exchange_rate', 1) }}"
                           class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10">
                </div>

                <div>
                    <label class="text-xs text-slate-300">Amount</label>
                    <input name="amount" value="{{ old('amount') }}"
                           class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" required>
                </div>

                <div class="md:col-span-3">
                    <label class="text-xs text-slate-300">Reference / Note</label>
                    <input name="reference" value="{{ old('reference') }}"
                           class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10"
                           placeholder="e.g. EcoCash ref, bank ref, POS ref">
                </div>
            </div>
        </div>

        {{-- Allocations (optional) --}}
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-4 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold">Allocate to Sales Invoices (Optional)</div>
                    <div class="text-xs text-slate-400">If you allocate, AR aging will reduce per invoice.</div>
                </div>
            </div>

            <div class="rounded-xl ring-1 ring-white/10 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-white/5">
                        <tr>
                            <th class="p-3 text-left">Invoice</th>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Total</th>
                            <th class="p-3 text-left">Allocate Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @php
                            // Build old allocations keyed by invoice_id for sticky form
                            $oldAllocs = collect(old('allocations', []))->keyBy('invoice_id');
                        @endphp

                        @forelse($openInvoices as $inv)
                            @php
                                $oldAmt = $oldAllocs->get($inv->id)['allocated_amount'] ?? '';
                            @endphp
                            <tr class="hover:bg-white/5">
                                <td class="p-3">
                                    <div class="font-medium">{{ $inv->invoice_no }}</div>
                                    <div class="text-xs text-slate-400">Customer ID: {{ $inv->customer_id }}</div>
                                </td>
                                <td class="p-3">{{ $inv->posting_date }}</td>
                                <td class="p-3">{{ $inv->currency }} {{ number_format((float)$inv->total,2) }}</td>
                                <td class="p-3">
                                    <input type="hidden" name="allocations[{{ $loop->index }}][invoice_id]" value="{{ $inv->id }}">
                                    <input name="allocations[{{ $loop->index }}][allocated_amount]"
                                           value="{{ $oldAmt }}"
                                           class="w-full px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10"
                                           placeholder="0.00">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-4 text-slate-300" colspan="4">No submitted invoices found to allocate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="text-[11px] text-slate-400">
                Only enter amounts for the invoices you want to allocate. Leave others blank.
            </div>
        </div>

        <button class="px-4 py-2 rounded-lg bg-indigo-500/20 hover:bg-indigo-500/30 ring-1 ring-indigo-400/30 text-sm">
            Save Receipt (Posts to GL)
        </button>
    </form>

</div>
@endsection
