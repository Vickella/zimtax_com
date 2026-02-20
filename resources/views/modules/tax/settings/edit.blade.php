@extends('layouts.erp')
@section('page_title','Tax Settings')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden">
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div>
                <div class="text-base font-semibold">Tax Settings</div>
                <div class="text-xs text-slate-300">Configure rates and GL mappings for ZIMRA outputs.</div>
            </div>
            <a href="{{ route('modules.tax.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
        </div>

        @if(session('success'))
            <div class="p-3 text-xs bg-emerald-500/10 ring-1 ring-emerald-500/20 text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('modules.tax.settings.update') }}" class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            @csrf

            <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
                <div class="text-sm font-semibold mb-3">Rates</div>

                <label class="text-xs text-slate-300">VAT Rate</label>
                <input name="vat_rate" value="{{ old('vat_rate', $settings->vat_rate ?? 0.155) }}"
                       class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />

                <div class="h-3"></div>

                <label class="text-xs text-slate-300">Income Tax Rate</label>
                <input name="income_tax_rate" value="{{ old('income_tax_rate', $settings->income_tax_rate ?? 0.2575) }}"
                       class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
            </div>

            <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4">
                <div class="text-sm font-semibold mb-3">VAT GL Mapping (by COA code)</div>

                <label class="text-xs text-slate-300">VAT Output Account Code</label>
                <input name="vat_output_account_code" value="{{ old('vat_output_account_code', $settings->vat_output_account_code) }}"
                       class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" placeholder="e.g. 2100-VAT-OUT"/>

                <div class="h-3"></div>

                <label class="text-xs text-slate-300">VAT Input Account Code</label>
                <input name="vat_input_account_code" value="{{ old('vat_input_account_code', $settings->vat_input_account_code) }}"
                       class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" placeholder="e.g. 2210-VAT-IN"/>
            </div>

            <div class="rounded-xl ring-1 ring-white/10 bg-white/5 p-4 md:col-span-2">
                <div class="text-sm font-semibold mb-3">QPD Percentages + Due Dates</div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs text-slate-300">Q1 % (10%)</label>
                        <input name="qpd_q1_percent" value="{{ old('qpd_q1_percent', $settings->qpd_q1_percent) }}"
                               class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                        <label class="text-xs text-slate-300">Q1 Due</label>
                        <input type="date" name="qpd_q1_due" value="{{ old('qpd_q1_due', optional($settings->qpd_q1_due)->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                    </div>

                    <div>
                        <label class="text-xs text-slate-300">Q2 % (25%)</label>
                        <input name="qpd_q2_percent" value="{{ old('qpd_q2_percent', $settings->qpd_q2_percent) }}"
                               class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                        <label class="text-xs text-slate-300">Q2 Due</label>
                        <input type="date" name="qpd_q2_due" value="{{ old('qpd_q2_due', optional($settings->qpd_q2_due)->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                    </div>

                    <div>
                        <label class="text-xs text-slate-300">Q3 % (30%)</label>
                        <input name="qpd_q3_percent" value="{{ old('qpd_q3_percent', $settings->qpd_q3_percent) }}"
                               class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                        <label class="text-xs text-slate-300">Q3 Due</label>
                        <input type="date" name="qpd_q3_due" value="{{ old('qpd_q3_due', optional($settings->qpd_q3_due)->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                    </div>

                    <div>
                        <label class="text-xs text-slate-300">Q4 % (35%)</label>
                        <input name="qpd_q4_percent" value="{{ old('qpd_q4_percent', $settings->qpd_q4_percent) }}"
                               class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                        <label class="text-xs text-slate-300">Q4 Due</label>
                        <input type="date" name="qpd_q4_due" value="{{ old('qpd_q4_due', optional($settings->qpd_q4_due)->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 flex justify-end gap-2">
                <a href="{{ route('modules.tax.index') }}"
                   class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Cancel</a>
                <button class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
