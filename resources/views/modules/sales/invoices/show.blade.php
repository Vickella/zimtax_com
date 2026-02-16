@extends('layouts.erp')
@section('page_title','Sales Invoice')

@section('content')
<div class="h-full overflow-auto space-y-4">

    @if(session('ok'))
        <div class="p-3 rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20 text-emerald-200 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-semibold">{{ $invoice->invoice_no }}</div>
                <div class="text-sm text-slate-300">Status: {{ $invoice->status }}</div>
            </div>

            <div class="flex gap-2">
                @if($invoice->status === 'DRAFT')
                    <a href="{{ route('modules.sales.invoices.edit',$invoice) }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">Edit</a>

                    <form method="POST" action="{{ route('modules.sales.invoices.submit',$invoice) }}">
                        @csrf
                        <button class="px-3 py-2 rounded-lg bg-indigo-500/20 hover:bg-indigo-500/30 ring-1 ring-indigo-400/30 text-sm">
                            Submit & Post
                        </button>
                    </form>

                    <form method="POST" action="{{ route('modules.sales.invoices.cancel',$invoice) }}">
                        @csrf
                        <button class="px-3 py-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 ring-1 ring-red-400/20 text-sm">
                            Cancel
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            <div>Posting Date: <span class="text-slate-200">{{ $invoice->posting_date }}</span></div>
            <div>Due Date: <span class="text-slate-200">{{ $invoice->due_date }}</span></div>
            <div>Customer ID: <span class="text-slate-200">{{ $invoice->customer_id }}</span></div>
        </div>

        <div class="mt-3 text-sm">
            Total: <span class="text-slate-200">{{ $invoice->currency }} {{ number_format((float)$invoice->total,2) }}</span>
        </div>
    </div>

    <div class="rounded-xl ring-1 ring-white/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-white/5">
                <tr>
                    <th class="p-3 text-left">Item</th>
                    <th class="p-3 text-left">Warehouse</th>
                    <th class="p-3 text-left">Qty</th>
                    <th class="p-3 text-left">Rate</th>
                    <th class="p-3 text-left">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach($invoice->lines as $l)
                <tr>
                    <td class="p-3">{{ $l->item?->name ?? $l->item_id }}</td>
                    <td class="p-3">{{ $l->warehouse?->name ?? $l->warehouse_id }}</td>
                    <td class="p-3">{{ $l->qty }}</td>
                    <td class="p-3">{{ $l->rate }}</td>
                    <td class="p-3">{{ number_format((float)$l->amount,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
