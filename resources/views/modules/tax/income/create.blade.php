@extends('layouts.erp')
@section('page_title','New ITF12C')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden flex flex-col" style="height: calc(100vh - 140px);">
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div>
                <div class="text-base font-semibold">New Income Tax Return (ITF12C)</div>
                <div class="text-xs text-slate-300">Choose period → computed from P&L (INCOME/EXPENSE types).</div>
            </div>
            <a href="{{ route('modules.tax.income.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
        </div>

        <form method="POST" action="{{ route('modules.tax.income.store') }}" class="flex-1 flex flex-col min-h-0">
            @csrf
            <div class="flex-1 min-h-0 overflow-y-auto p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
                    <label class="text-xs text-slate-300">Period Start</label>
                    <input type="date" name="period_start" value="{{ old('period_start') }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" required />

                    <div class="h-3"></div>

                    <label class="text-xs text-slate-300">Period End</label>
                    <input type="date" name="period_end" value="{{ old('period_end') }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" required />
                </div>

                <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
                    <div class="text-sm font-semibold mb-3">Adjustments</div>

                    <label class="text-xs text-slate-300">Add backs</label>
                    <input name="add_backs" value="{{ old('add_backs', 0) }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />

                    <div class="h-3"></div>

                    <label class="text-xs text-slate-300">Deductions</label>
                    <input name="deductions" value="{{ old('deductions', 0) }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />

                    <div class="h-3"></div>

                    <label class="text-xs text-slate-300">Override taxable income (optional)</label>
                    <input name="override_taxable_income" value="{{ old('override_taxable_income') }}"
                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />

                    <div class="h-3"></div>
                    <label class="text-xs text-slate-300">Notes</label>
                    <textarea name="notes" rows="3"
                              class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="shrink-0 bg-slate-950/80 backdrop-blur border-t border-white/10 p-3">
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('modules.tax.income.index') }}"
                       class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Cancel</a>
                    <button class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                        Create ITF12C
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
