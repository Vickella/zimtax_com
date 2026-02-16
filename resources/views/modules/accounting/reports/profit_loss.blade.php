@extends('layouts.app')

@section('page_title','Profit & Loss')

@section('content')
<form class="flex flex-wrap items-end gap-3 mb-4">
    <div>
        <label class="text-xs text-slate-300">From</label>
        <input type="date" name="from" value="{{ $from }}"
               class="mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
    </div>

    <div>
        <label class="text-xs text-slate-300">To</label>
        <input type="date" name="to" value="{{ $to }}"
               class="mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
    </div>

    <button class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
        Run
    </button>

    <a href="{{ route('modules.accounting.reports.profit-loss.csv', ['from'=>$from,'to'=>$to]) }}"
       class="ml-auto px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
        Download CSV
    </a>
</form>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
        <div class="text-xs text-slate-400">Total Income</div>
        <div class="text-2xl font-semibold mt-1">{{ number_format((float)$total_income, 2) }}</div>
    </div>

    <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
        <div class="text-xs text-slate-400">Total Expenses</div>
        <div class="text-2xl font-semibold mt-1">{{ number_format((float)$total_expenses, 2) }}</div>
    </div>

    <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
        <div class="text-xs text-slate-400">Net Profit</div>
        <div class="text-2xl font-semibold mt-1">{{ number_format((float)$net_profit, 2) }}</div>
    </div>
</div>

@php
    $incomeRows = collect($rows)->where('account_type','INCOME')->values();
    $expenseRows = collect($rows)->where('account_type','EXPENSE')->values();
@endphp

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    {{-- Income --}}
    <div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
        <div class="px-4 py-3 bg-white/5 text-sm font-semibold">Income</div>
        <table class="w-full text-sm">
            <thead class="bg-white/5">
            <tr>
                <th class="p-3 text-left">Code</th>
                <th class="p-3 text-left">Account</th>
                <th class="p-3 text-right">Credit</th>
                <th class="p-3 text-right">Debit</th>
                <th class="p-3 text-right">Balance</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
            @forelse($incomeRows as $r)
                <tr>
                    <td class="p-3 font-mono">{{ $r['code'] }}</td>
                    <td class="p-3">{{ $r['name'] }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['credit'],2) }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['debit'],2) }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['balance'],2) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="p-3 text-slate-400" colspan="5">No income transactions in this period.</td>
                </tr>
            @endforelse
            </tbody>
            <tfoot class="bg-white/5">
            <tr>
                <th class="p-3 text-left" colspan="4">Total Income</th>
                <th class="p-3 text-right">{{ number_format((float)$total_income,2) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>

    {{-- Expenses --}}
    <div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
        <div class="px-4 py-3 bg-white/5 text-sm font-semibold">Expenses</div>
        <table class="w-full text-sm">
            <thead class="bg-white/5">
            <tr>
                <th class="p-3 text-left">Code</th>
                <th class="p-3 text-left">Account</th>
                <th class="p-3 text-right">Debit</th>
                <th class="p-3 text-right">Credit</th>
                <th class="p-3 text-right">Balance</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
            @forelse($expenseRows as $r)
                <tr>
                    <td class="p-3 font-mono">{{ $r['code'] }}</td>
                    <td class="p-3">{{ $r['name'] }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['debit'],2) }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['credit'],2) }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['balance'],2) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="p-3 text-slate-400" colspan="5">No expense transactions in this period.</td>
                </tr>
            @endforelse
            </tbody>
            <tfoot class="bg-white/5">
            <tr>
                <th class="p-3 text-left" colspan="4">Total Expenses</th>
                <th class="p-3 text-right">{{ number_format((float)$total_expenses,2) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
