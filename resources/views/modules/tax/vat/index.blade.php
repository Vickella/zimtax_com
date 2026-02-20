@extends('layouts.erp')
@section('page_title','VAT Returns')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="text-lg font-semibold">VAT Returns (VAT 7)</div>
            <div class="text-xs text-slate-300">Computed from GL VAT accounts + export to print format.</div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('modules.tax.index') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Back</a>
            <a href="{{ route('modules.tax.vat.create') }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">New VAT Return</a>
        </div>
    </div>

    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="text-xs text-slate-300">
                <tr class="border-b border-white/10">
                    <th class="text-left px-4 py-3">Period</th>
                    <th class="text-right px-4 py-3">Output VAT</th>
                    <th class="text-right px-4 py-3">Input VAT</th>
                    <th class="text-right px-4 py-3">Net</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($returns as $r)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $r->period_start?->format('Y-m-d') }} → {{ $r->period_end?->format('Y-m-d') }}</div>
                            <div class="text-xs text-slate-300">Rate: {{ number_format($r->vat_rate*100,2) }}%</div>
                        </td>
                        <td class="px-4 py-3 text-right">{{ number_format($r->output_vat,2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($r->input_vat,2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($r->net_vat_payable,2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('modules.tax.vat.show',$r->id) }}"
                               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-xs text-slate-300">No VAT returns yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-3">
            {{ $returns->links() }}
        </div>
    </div>
</div>
@endsection
