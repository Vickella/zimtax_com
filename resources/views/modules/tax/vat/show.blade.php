@extends('layouts.erp')
@section('page_title','VAT Return')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="text-lg font-semibold">VAT Return</div>
            <div class="text-xs text-slate-300">{{ $vatReturn->period_start?->format('Y-m-d') }} → {{ $vatReturn->period_end?->format('Y-m-d') }}</div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('modules.tax.vat.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
            <a href="{{ route('modules.tax.vat.pdf',$vatReturn->id) }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">Download PDF (VAT7)</a>
            <a href="{{ route('modules.tax.vat.excel',$vatReturn->id) }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Export Excel</a>
        </div>
    </div>

    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
            <div class="text-sm font-semibold">Computed</div>
            <div class="mt-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-300">Taxable Sales</span><span>{{ number_format($vatReturn->taxable_sales,2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-300">Output VAT</span><span>{{ number_format($vatReturn->output_vat,2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-300">Taxable Purchases</span><span>{{ number_format($vatReturn->taxable_purchases,2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-300">Input VAT</span><span>{{ number_format($vatReturn->input_vat,2) }}</span></div>
                <div class="border-t border-white/10 my-2"></div>
                <div class="flex justify-between font-semibold"><span>Net VAT Payable</span><span>{{ number_format($vatReturn->net_vat_payable,2) }}</span></div>
            </div>
        </div>

        <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
            <div class="text-sm font-semibold">Notes</div>
            <div class="text-xs text-slate-300 mt-2 whitespace-pre-line">{{ $vatReturn->notes ?: '—' }}</div>

            <div class="text-sm font-semibold mt-4">Source Snapshot</div>
            <pre class="text-[11px] mt-2 bg-black/30 ring-1 ring-white/10 rounded-lg p-3 overflow-auto">{{ json_encode($vatReturn->source_snapshot, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>
@endsection
