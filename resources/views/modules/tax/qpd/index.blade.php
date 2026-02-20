@extends('layouts.erp')
@section('page_title','QPD / ITF12B')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="text-lg font-semibold">QPD Forecasts (ITF12B)</div>
            <div class="text-xs text-slate-300">Forecast annual tax → compute quarterly payments (10/25/30/35).</div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('modules.tax.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
            <a href="{{ route('modules.tax.qpd.create') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">New QPD Projection</a>
        </div>
    </div>

    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="text-xs text-slate-300">
            <tr class="border-b border-white/10">
                <th class="text-left px-4 py-3">Tax Year</th>
                <th class="text-right px-4 py-3">Estimated Taxable Income</th>
                <th class="text-right px-4 py-3">Estimated Tax Payable</th>
                <th class="px-4 py-3"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
            @forelse($projections as $p)
                <tr>
                    <td class="px-4 py-3 font-semibold">{{ $p->tax_year }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($p->estimated_taxable_income,2) }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($p->estimated_tax_payable,2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('modules.tax.qpd.show',$p->id) }}"
                           class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-xs text-slate-300">No QPD projections yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $projections->links() }}</div>
    </div>
</div>
@endsection
