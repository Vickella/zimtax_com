@extends('layouts.erp')
@section('page_title','Income Tax / ITF12C')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="text-lg font-semibold">Income Tax Returns (ITF12C)</div>
            <div class="text-xs text-slate-300">Compute from GL P&L + export ITF12C PDF.</div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('modules.tax.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
            <a href="{{ route('modules.tax.income.create') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">New ITF12C</a>
        </div>
    </div>

    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="text-xs text-slate-300">
                <tr class="border-b border-white/10">
                    <th class="text-left px-4 py-3">Period</th>
                    <th class="text-right px-4 py-3">Taxable Income</th>
                    <th class="text-right px-4 py-3">Tax Payable</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($returns as $r)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $r->period_start?->format('Y-m-d') }} → {{ $r->period_end?->format('Y-m-d') }}</div>
                            <div class="text-xs text-slate-300">Rate: {{ number_format($r->income_tax_rate*100,2) }}%</div>
                        </td>
                        <td class="px-4 py-3 text-right">{{ number_format($r->taxable_income,2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($r->income_tax_payable,2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('modules.tax.income.show',$r->id) }}"
                               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-xs text-slate-300">No income tax returns yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-3">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
