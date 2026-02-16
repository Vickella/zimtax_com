@extends('layouts.app')

@section('page_title','Trial Balance')

@section('content')
<form class="flex flex-wrap items-end gap-3 mb-4">
    <div>
        <label class="text-xs text-slate-300">From</label>
        <input type="date" name="from" value="{{ $from }}"
               class="mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
    </div>
    <div>
        <label class="text-xs text-slate-300">To</label>
        <input type="date" name="to" value="{{ $to }}"
               class="mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
    </div>
    <button class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">Run</button>

    <a href="{{ route('modules.accounting.reports.trial-balance.csv', ['from'=>$from,'to'=>$to]) }}"
       class="ml-auto px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
        Download CSV
    </a>
</form>

<div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
    <table class="w-full text-sm">
        <thead class="bg-white/5">
        <tr>
            <th class="p-3 text-left w-[5%]">Code</th>
            <th class="p-3 text-left w-[25%]">Account</th>
            <th class="p-3 text-left w-[25%]">Type</th>
            <th class="p-3 text-right w-[10%]">Debit</th>
            <th class="p-3 text-right w-[10%]">Credit</th>
            <th class="p-3 text-right w-[10%]">Net</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-white/10">
        @foreach($rows as $r)
            <tr>
                <td class="p-3 font-mono truncate">{{ $r['code'] }}</td>
                <td class="p-3 truncate">{{ $r['name'] }}</td>
                <td class="p-3 truncate">{{ $r['type'] }}</td>
                <td class="p-3 text-right">{{ number_format((float)$r['debit'],2) }}</td>
                <td class="p-3 text-right">{{ number_format((float)$r['credit'],2) }}</td>
                <td class="p-3 text-right">{{ number_format((float)$r['net'],2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection