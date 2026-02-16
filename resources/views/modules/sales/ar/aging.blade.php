@extends('layouts.erp')
@section('page_title','AR Aging')

@section('content')
<div class="h-full overflow-auto space-y-4">

    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="text-xs text-slate-300">As of</label>
            <input type="date" name="as_of" value="{{ request('as_of', $data['as_of']) }}"
                   class="mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10">
        </div>

        <div>
            <label class="text-xs text-slate-300">Customer</label>
            <select name="customer_id" class="mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10">
                <option value="">All</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected(($customerId ?? null) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <button class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">Run</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3">Current<br><span class="text-slate-200">{{ number_format($data['buckets']['current'],2) }}</span></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3">1–30<br><span class="text-slate-200">{{ number_format($data['buckets']['1_30'],2) }}</span></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3">31–60<br><span class="text-slate-200">{{ number_format($data['buckets']['31_60'],2) }}</span></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3">61–90<br><span class="text-slate-200">{{ number_format($data['buckets']['61_90'],2) }}</span></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3">90+<br><span class="text-slate-200">{{ number_format($data['buckets']['90_plus'],2) }}</span></div>
    </div>

    <div class="rounded-xl ring-1 ring-white/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-white/5">
                <tr>
                    <th class="p-3 text-left">Invoice</th>
                    <th class="p-3 text-left">Due</th>
                    <th class="p-3 text-left">Days</th>
                    <th class="p-3 text-left">Outstanding</th>
                    <th class="p-3 text-left">Bucket</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach($data['rows'] as $r)
                <tr class="hover:bg-white/5">
                    <td class="p-3">
                        <a class="text-indigo-200 hover:underline" href="{{ route('sales.invoices.show',$r['invoice_id']) }}">{{ $r['invoice_no'] }}</a>
                    </td>
                    <td class="p-3">{{ $r['due_date'] }}</td>
                    <td class="p-3">{{ $r['days_overdue'] }}</td>
                    <td class="p-3">{{ number_format($r['outstanding'],2) }}</td>
                    <td class="p-3">{{ $r['bucket'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
