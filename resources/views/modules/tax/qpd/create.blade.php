@extends('layouts.erp')
@section('page_title','New QPD Projection')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden flex flex-col" style="height: calc(100vh - 140px);">
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div>
                <div class="text-base font-semibold">New QPD Projection (ITF12B)</div>
                <div class="text-xs text-slate-300">Enter base taxable income + growth forecast rate.</div>
            </div>
            <a href="{{ route('modules.tax.qpd.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
        </div>

        <form method="POST" action="{{ route('modules.tax.qpd.store') }}" class="flex-1 flex flex-col min-h-0">
            @csrf

            <div class="flex-1 min-h-0 overflow-y-auto p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
                    <label class="text-xs text-slate-300">Tax Year</label>
                    <input name="tax_year" value="{{ old('tax_year', date('Y')) }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" required />

                    <div class="h-3"></div>

                    <label class="text-xs text-slate-300">Base Estimated Taxable Income</label>
                    <input name="base_taxable_income" value="{{ old('base_taxable_income') }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" required />

                    <div class="h-3"></div>

                    <label class="text-xs text-slate-300">Forecast Growth Rate (e.g 0.10 = 10%)</label>
                    <input name="growth_rate" value="{{ old('growth_rate', 0) }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                </div>

                <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
                    <label class="text-xs text-slate-300">Notes</label>
                    <textarea name="notes" rows="6"
                              class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                    <div class="text-[11px] text-slate-300 mt-2">
                        QPD calculation uses configured percentages and deducts already paid amounts.
                    </div>
                </div>
            </div>

            <div class="shrink-0 bg-slate-950/80 backdrop-blur border-t border-white/10 p-3">
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('modules.tax.qpd.index') }}"
                       class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Cancel</a>
                    <button class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                        Create Projection
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
