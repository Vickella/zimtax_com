@extends('layouts.erp')
@section('page_title','QPD / ITF12B')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="text-lg font-semibold">QPD Projection — {{ $projection->tax_year }}</div>
            <div class="text-xs text-slate-300">Income tax rate: {{ number_format(($projection->income_tax_rate ?? 0)*100,2) }}%</div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('modules.tax.qpd.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
            <a href="{{ route('modules.tax.qpd.excel',$projection->id) }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Export Excel</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
        <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 p-4">
            <div class="text-sm font-semibold">Projection</div>
            <div class="mt-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-300">Base Taxable Income</span><span>{{ number_format($projection->base_taxable_income,2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-300">Growth Rate</span><span>{{ number_format(($projection->growth_rate ?? 0)*100,2) }}%</span></div>
                <div class="flex justify-between"><span class="text-slate-300">Estimated Taxable Income</span><span>{{ number_format($summary['estimated_taxable_income'],2) }}</span></div>
                <div class="border-t border-white/10 my-2"></div>
                <div class="flex justify-between font-semibold"><span>Estimated Tax Payable</span><span>{{ number_format($summary['estimated_tax_payable'],2) }}</span></div>
            </div>
        </div>

        <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 p-4">
            <div class="text-sm font-semibold">Notes</div>
            <div class="text-xs text-slate-300 mt-2 whitespace-pre-line">{{ $projection->notes ?: '—' }}</div>
        </div>
    </div>

    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="text-xs text-slate-300">
                <tr class="border-b border-white/10">
                    <th class="text-left px-4 py-3">Quarter</th>
                    <th class="text-right px-4 py-3">Cumulative %</th>
                    <th class="text-right px-4 py-3">Cumulative Due</th>
                    <th class="text-right px-4 py-3">Paid To Date</th>
                    <th class="text-right px-4 py-3">Balance Due</th>
                    <th class="text-right px-4 py-3">Due Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach($summary['quarters'] as $qNo => $q)
                    <tr>
                        <td class="px-4 py-3 font-semibold">Q{{ $qNo }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($q['cumulative_percent']*100,2) }}%</td>
                        <td class="px-4 py-3 text-right">{{ number_format($q['cumulative_due'],2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($q['paid_to_date'],2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($q['balance_due'],2) }}</td>
                        <td class="px-4 py-3 text-right">{{ $q['due_date'] }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('modules.tax.qpd.pdf', [$projection->id, $qNo]) }}"
                                   class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">PDF ITF12B</a>
                                <button type="button"
                                        onclick="document.getElementById('pay-q{{ $qNo }}').classList.toggle('hidden')"
                                        class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Pay</button>
                            </div>
                        </td>
                    </tr>
                    <tr id="pay-q{{ $qNo }}" class="hidden">
                        <td colspan="7" class="px-4 py-3">
                            <form method="POST" action="{{ route('modules.tax.qpd.pay', [$projection->id, $qNo]) }}"
                                  class="grid grid-cols-1 md:grid-cols-4 gap-2">
                                @csrf
                                <div>
                                    <label class="text-xs text-slate-300">Payment Date</label>
                                    <input type="date" name="payment_date"
                                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" required />
                                </div>
                                <div>
                                    <label class="text-xs text-slate-300">Amount</label>
                                    <input name="amount"
                                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" required />
                                </div>
                                <div>
                                    <label class="text-xs text-slate-300">Reference</label>
                                    <input name="reference"
                                           class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm" />
                                </div>
                                <div class="flex items-end justify-end">
                                    <button class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                                        Record Payment
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
