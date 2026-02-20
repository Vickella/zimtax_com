@extends('layouts.erp')
@section('page_title','New VAT Return')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden flex flex-col" style="height: calc(100vh - 140px);">
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div>
                <div class="text-base font-semibold">New VAT Return (VAT 7)</div>
                <div class="text-xs text-slate-300">Choose period → system computes from GL VAT accounts.</div>
            </div>
            <a href="{{ route('modules.tax.vat.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
        </div>

        <form method="POST" action="{{ route('modules.tax.vat.store') }}" class="flex-1 flex flex-col min-h-0">
            @csrf
            <div class="flex-1 min-h-0 overflow-y-auto p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
                    <div class="text-sm font-semibold mb-3">Period</div>

                    <label class="text-xs text-slate-300">Period Start</label>
                    <input type="date" name="period_start" value="{{ old('period_start') }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" required />

                    <div class="h-3"></div>

                    <label class="text-xs text-slate-300">Period End</label>
                    <input type="date" name="period_end" value="{{ old('period_end') }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" required />
                </div>

                <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
                    <div class="text-sm font-semibold mb-3">Overrides (optional)</div>

                    <label class="text-xs text-slate-300">Override Taxable Sales</label>
                    <input name="override_taxable_sales" value="{{ old('override_taxable_sales') }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />

                    <div class="h-3"></div>

                    <label class="text-xs text-slate-300">Override Taxable Purchases</label>
                    <input name="override_taxable_purchases" value="{{ old('override_taxable_purchases') }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />

                    <div class="h-3"></div>

                    <label class="text-xs text-slate-300">Notes</label>
                    <textarea name="notes" rows="3"
                              class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="shrink-0 bg-slate-950/80 backdrop-blur border-t border-white/10 p-3">
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('modules.tax.vat.index') }}"
                       class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Cancel</a>
                    <button class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                        Create VAT Return
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
