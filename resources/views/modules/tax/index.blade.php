@extends('layouts.erp')
@section('page_title','Tax')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden">
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div>
                <div class="text-base font-semibold">Tax Module</div>
                <div class="text-xs text-slate-300">ZIMRA print-format returns and compliance outputs.</div>
            </div>
            <a href="{{ route('dashboard') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <a href="{{ route('modules.tax.vat.index') }}"
               class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4 hover:bg-white/10">
                <div class="text-sm font-semibold">VAT Return (VAT 7)</div>
                <div class="text-xs text-slate-300 mt-1">Compute from GL + export exact VAT7 print PDF.</div>
            </a>

            <a href="{{ route('modules.tax.qpd.index') }}"
               class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4 hover:bg-white/10">
                <div class="text-sm font-semibold">QPDs (ITF12B)</div>
                <div class="text-xs text-slate-300 mt-1">Forecast + compute quarter amounts (10/25/30/35).</div>
            </a>

            <a href="{{ route('modules.tax.income.index') }}"
               class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4 hover:bg-white/10">
                <div class="text-sm font-semibold">Income Tax (ITF12C)</div>
                <div class="text-xs text-slate-300 mt-1">Compute taxable income + export ITF12C print PDF.</div>
            </a>

            <a href="{{ route('modules.tax.settings.edit') }}"
               class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4 hover:bg-white/10">
                <div class="text-sm font-semibold">Tax Settings</div>
                <div class="text-xs text-slate-300 mt-1">Rates, VAT accounts, QPD due dates, percentages.</div>
            </a>
        </div>
    </div>
</div>
@endsection
