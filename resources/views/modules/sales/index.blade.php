@extends('layouts.erp')

@section('page_title', 'Sales')

@section('content')
    <div class="h-full overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-semibold">Sales</h1>
                <p class="text-sm text-slate-300">Create invoices, manage customers, receipts and AR reports.</p>
            </div>

            <a href="{{ route('modules.sales.invoices.create') }}"
               class="rounded-lg px-4 py-2 text-sm bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                + New Invoice
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Customers --}}
            <a href="{{ route('modules.sales.customers.index') }}"
               class="rounded-2xl bg-black/10 ring-1 ring-white/10 p-4 hover:bg-white/5 transition">
                <div class="text-2xl mb-2">👥</div>
                <div class="font-semibold">Customers</div>
                <div class="text-xs text-slate-300 mt-1">Create and manage customer master data</div>
            </a>

            {{-- Sales Invoices --}}
            <a href="{{ route('modules.sales.invoices.index') }}"
               class="rounded-2xl bg-black/10 ring-1 ring-white/10 p-4 hover:bg-white/5 transition">
                <div class="text-2xl mb-2">🧾</div>
                <div class="font-semibold">Sales Invoices</div>
                <div class="text-xs text-slate-300 mt-1">Draft, submit, cancel invoices</div>
            </a>

            {{-- Receipts --}}
            <a href="{{ route('modules.sales.receipts.index') }}"
               class="rounded-2xl bg-black/10 ring-1 ring-white/10 p-4 hover:bg-white/5 transition">
                <div class="text-2xl mb-2">💰</div>
                <div class="font-semibold">Receipts</div>
                <div class="text-xs text-slate-300 mt-1">Capture customer payments and allocations</div>
            </a>

            {{-- AR Aging --}}
            <a href="{{ route('modules.sales.ar.aging') }}"
               class="rounded-2xl bg-black/10 ring-1 ring-white/10 p-4 hover:bg-white/5 transition">
                <div class="text-2xl mb-2">📊</div>
                <div class="font-semibold">AR Aging</div>
                <div class="text-xs text-slate-300 mt-1">Receivable aging analysis and overdue balances</div>
            </a>
        </div>
    </div>
@endsection
