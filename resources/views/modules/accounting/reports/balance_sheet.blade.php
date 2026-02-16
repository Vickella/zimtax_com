@extends('layouts.app')

@section('page_title','Balance Sheet')

@section('content')
<form class="flex flex-wrap items-end gap-3 mb-4">
    <div>
        <label class="text-xs text-slate-300">As Of</label>
        <input type="date" name="as_of" value="{{ $asOf }}"
               class="mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
    </div>

    <button class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
        Run
    </button>

    <a href="{{ route('modules.accounting.reports.balance-sheet.csv', ['as_of'=>$asOf]) }}"
       class="ml-auto px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
        Download CSV
    </a>
</form>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
        <div class="text-xs text-slate-400">Total Assets</div>
        <div class="text-2xl font-semibold mt-1">{{ number_format((float)$total_assets, 2) }}</div>
    </div>

    <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
        <div class="text-xs text-slate-400">Total Liabilities</div>
        <div class="text-2xl font-semibold mt-1">{{ number_format((float)$total_liabilities, 2) }}</div>
    </div>

    <div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4">
        <div class="text-xs text-slate-400">Liabilities + Equity</div>
        <div class="text-2xl font-semibold mt-1">{{ number_format((float)$liabilities_plus_equity, 2) }}</div>
    </div>
</div>

@php
    $assetsRows = collect($rows)->where('account_type','ASSET')->values();
    $liabRows   = collect($rows)->where('account_type','LIABILITY')->values();
    $equityRows = collect($rows)->where('account_type','EQUITY')->values();

    // Presentation:
    // Assets: show net as Dr - Cr
    // Liab/Equity: show as (Cr - Dr) => negative of net
    $liabTotal = (float)$total_liabilities;
    $equityTotal = (float)$total_equity;
@endphp

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    {{-- Assets --}}
    <div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
        <div class="px-4 py-3 bg-white/5 text-sm font-semibold">Assets</div>
        <table class="w-full text-sm">
            <thead class="bg-white/5">
            <tr>
                <th class="p-3 text-left">Code</th>
                <th class="p-3 text-left">Account</th>
                <th class="p-3 text-right">Debit</th>
                <th class="p-3 text-right">Credit</th>
                <th class="p-3 text-right">Net</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
            @forelse($assetsRows as $r)
                <tr>
                    <td class="p-3 font-mono">{{ $r['code'] }}</td>
                    <td class="p-3">{{ $r['name'] }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['debit'],2) }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['credit'],2) }}</td>
                    <td class="p-3 text-right">{{ number_format((float)$r['net'],2) }}</td>
                </tr>
            @empty
                <tr><td class="p-3 text-slate-400" colspan="5">No asset balances.</td></tr>
            @endforelse
            </tbody>
            <tfoot class="bg-white/5">
            <tr>
                <th class="p-3 text-left" colspan="4">Total Assets</th>
                <th class="p-3 text-right">{{ number_format((float)$total_assets,2) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>

    {{-- Liabilities & Equity --}}
    <div class="space-y-4">
        <div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
            <div class="px-4 py-3 bg-white/5 text-sm font-semibold">Liabilities</div>
            <table class="w-full text-sm">
                <thead class="bg-white/5">
                <tr>
                    <th class="p-3 text-left">Code</th>
                    <th class="p-3 text-left">Account</th>
                    <th class="p-3 text-right">Debit</th>
                    <th class="p-3 text-right">Credit</th>
                    <th class="p-3 text-right">Balance (Cr)</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                @forelse($liabRows as $r)
                    @php $bal = -(float)$r['net']; @endphp
                    <tr>
                        <td class="p-3 font-mono">{{ $r['code'] }}</td>
                        <td class="p-3">{{ $r['name'] }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r['debit'],2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r['credit'],2) }}</td>
                        <td class="p-3 text-right">{{ number_format($bal,2) }}</td>
                    </tr>
                @empty
                    <tr><td class="p-3 text-slate-400" colspan="5">No liability balances.</td></tr>
                @endforelse
                </tbody>
                <tfoot class="bg-white/5">
                <tr>
                    <th class="p-3 text-left" colspan="4">Total Liabilities</th>
                    <th class="p-3 text-right">{{ number_format((float)$total_liabilities,2) }}</th>
                </tr>
                </tfoot>
            </table>
        </div>

        <div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
            <div class="px-4 py-3 bg-white/5 text-sm font-semibold">Equity</div>
            <table class="w-full text-sm">
                <thead class="bg-white/5">
                <tr>
                    <th class="p-3 text-left">Code</th>
                    <th class="p-3 text-left">Account</th>
                    <th class="p-3 text-right">Debit</th>
                    <th class="p-3 text-right">Credit</th>
                    <th class="p-3 text-right">Balance (Cr)</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                @forelse($equityRows as $r)
                    @php $bal = -(float)$r['net']; @endphp
                    <tr>
                        <td class="p-3 font-mono">{{ $r['code'] }}</td>
                        <td class="p-3">{{ $r['name'] }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r['debit'],2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r['credit'],2) }}</td>
                        <td class="p-3 text-right">{{ number_format($bal,2) }}</td>
                    </tr>
                @empty
                    <tr><td class="p-3 text-slate-400" colspan="5">No equity balances.</td></tr>
                @endforelse
                </tbody>
                <tfoot class="bg-white/5">
                <tr>
                    <th class="p-3 text-left" colspan="4">Total Equity</th>
                    <th class="p-3 text-right">{{ number_format((float)$total_equity,2) }}</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 rounded-xl ring-1 ring-white/10 bg-black/10 p-4 flex items-center justify-between">
    <div class="text-sm text-slate-300">Check</div>
    <div class="text-sm">
        <span class="text-slate-400">Assets:</span> {{ number_format((float)$total_assets,2) }}
        <span class="mx-2 text-slate-500">|</span>
        <span class="text-slate-400">L+E:</span> {{ number_format((float)$liabilities_plus_equity,2) }}
    </div>
</div>
@endsection
