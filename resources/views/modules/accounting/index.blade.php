@extends('layouts.app')

@section('page_title','Accounting')

@section('content')
<div class="h-full flex flex-col gap-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div class="space-y-1">
            <h1 class="text-xl font-semibold tracking-tight">Accounting</h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('modules.accounting.journals.create') }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-100 ring-1 ring-indigo-300/20 transition">
                <span class="text-base">＋</span>
                <span>New Journal</span>
            </a>

            <a href="{{ route('modules.accounting.payments.create') }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm bg-white/10 hover:bg-white/15 text-slate-100 ring-1 ring-white/10 transition">
                <span class="text-base">＋</span>
                <span>New Payment</span>
            </a>
        </div>
    </div>

    {{-- Primary modules --}}
    <div class="rounded-2xl ring-1 ring-white/10 bg-black/10 overflow-hidden">
        <div class="p-5 border-b border-white/10">
            <div class="flex items-center justify-between gap-3">
                <div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 p-5">
            <a href="{{ route('modules.accounting.chart.index') }}"
               class="group rounded-2xl ring-1 ring-white/10 bg-white/5 hover:bg-white/10 transition p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold">Chart of Accounts</div>
                        <div class="text-xs text-slate-300 mt-1">
                            Maintain accounts used for posting and reporting.
                        </div>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/5 ring-1 ring-white/10 px-3 py-2 text-lg group-hover:bg-white/10">
                        📚
                    </div>
                </div>
                <div class="mt-4 text-xs text-indigo-200/90 group-hover:text-indigo-100">
                    Open →
                </div>
            </a>

            <a href="{{ route('modules.accounting.journals.index') }}"
               class="group rounded-2xl ring-1 ring-white/10 bg-white/5 hover:bg-white/10 transition p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold">Journal Entries</div>
                        <div class="text-xs text-slate-300 mt-1">
                            Create, review, and post journals to the general ledger.
                        </div>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/5 ring-1 ring-white/10 px-3 py-2 text-lg group-hover:bg-white/10">
                        🧾
                    </div>
                </div>
                <div class="mt-4 text-xs text-indigo-200/90 group-hover:text-indigo-100">
                    Open →
                </div>
            </a>

            <a href="{{ route('modules.accounting.payments.index') }}"
               class="group rounded-2xl ring-1 ring-white/10 bg-white/5 hover:bg-white/10 transition p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold">Payments</div>
                        <div class="text-xs text-slate-300 mt-1">
                            Capture receipts and payments and keep balances aligned.
                        </div>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/5 ring-1 ring-white/10 px-3 py-2 text-lg group-hover:bg-white/10">
                        💳
                    </div>
                </div>
                <div class="mt-4 text-xs text-indigo-200/90 group-hover:text-indigo-100">
                    Open →
                </div>
            </a>
        </div>
    </div>

    {{-- Reports --}}
    <div class="rounded-2xl ring-1 ring-white/10 bg-black/10 overflow-hidden">
        <div class="p-5 border-b border-white/10">
            <div class="flex items-start justify-between gap-4">
                <div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 p-5">

            {{-- Trial Balance --}}
            <div class="rounded-2xl ring-1 ring-white/10 bg-white/5 hover:bg-white/10 transition p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold">Trial Balance</div>
                        <div class="text-xs text-slate-300 mt-1">
                            Account balances for a selected period.
                        </div>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/5 ring-1 ring-white/10 px-3 py-2 text-lg">
                        🧮
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('modules.accounting.reports.trial-balance') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs bg-white/10 hover:bg-white/15 ring-1 ring-white/10 transition">
                        View
                    </a>
                    <a href="{{ route('modules.accounting.reports.trial-balance.csv') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs bg-black/20 hover:bg-black/30 ring-1 ring-white/10 transition">
                        CSV
                    </a>
                </div>
            </div>

            {{-- General Ledger --}}
            <div class="rounded-2xl ring-1 ring-white/10 bg-white/5 hover:bg-white/10 transition p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold">General Ledger</div>
                        <div class="text-xs text-slate-300 mt-1">
                            Transactions per account with running totals.
                        </div>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/5 ring-1 ring-white/10 px-3 py-2 text-lg">
                        📒
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('modules.accounting.reports.general-ledger') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs bg-white/10 hover:bg-white/15 ring-1 ring-white/10 transition">
                        View
                    </a>
                    <a href="{{ route('modules.accounting.reports.general-ledger.csv') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs bg-black/20 hover:bg-black/30 ring-1 ring-white/10 transition">
                        CSV
                    </a>
                </div>
            </div>

            {{-- Profit & Loss --}}
            <div class="rounded-2xl ring-1 ring-white/10 bg-white/5 hover:bg-white/10 transition p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold">Profit &amp; Loss</div>
                        <div class="text-xs text-slate-300 mt-1">
                            Income and expenses for a date range.
                        </div>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/5 ring-1 ring-white/10 px-3 py-2 text-lg">
                        📈
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('modules.accounting.reports.profit-loss') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs bg-white/10 hover:bg-white/15 ring-1 ring-white/10 transition">
                        View
                    </a>
                    <a href="{{ route('modules.accounting.reports.profit-loss.csv') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs bg-black/20 hover:bg-black/30 ring-1 ring-white/10 transition">
                        CSV
                    </a>
                </div>
            </div>

            {{-- Balance Sheet --}}
            <div class="rounded-2xl ring-1 ring-white/10 bg-white/5 hover:bg-white/10 transition p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold">Balance Sheet</div>
                        <div class="text-xs text-slate-300 mt-1">
                            Assets, liabilities, and equity at a date.
                        </div>
                    </div>
                    <div class="shrink-0 rounded-xl bg-white/5 ring-1 ring-white/10 px-3 py-2 text-lg">
                        🏦
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('modules.accounting.reports.balance-sheet') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs bg-white/10 hover:bg-white/15 ring-1 ring-white/10 transition">
                        View
                    </a>
                    <a href="{{ route('modules.accounting.reports.balance-sheet.csv') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs bg-black/20 hover:bg-black/30 ring-1 ring-white/10 transition">
                        CSV
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
