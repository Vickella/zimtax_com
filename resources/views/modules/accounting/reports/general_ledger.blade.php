@extends('layouts.app')

@section('page_title','General Ledger')

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

    <div class="min-w-[260px]">
        <label class="text-xs text-slate-300">Account</label>
        <select name="account_id"
                class="mt-1 w-full px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
            <option value="">All accounts</option>
            @foreach($accounts as $a)
                <option value="{{ $a->id }}" @selected((string)$accountId === (string)$a->id)>
                    {{ $a->code }} - {{ $a->name }}
                </option>
            @endforeach
        </select>
    </div>

    <button class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
        Run
    </button>
</form>

<div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
    <table class="w-full text-sm">
        <thead class="bg-white/5">
        <tr>
            <th class="p-3 text-left">Date</th>
            <th class="p-3 text-left">Account</th>
            <th class="p-3 text-right">Debit</th>
            <th class="p-3 text-right">Credit</th>
            <th class="p-3 text-left">Currency</th>
            <th class="p-3 text-left">Journal</th>
            <th class="p-3 text-left">Party</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-white/10">
        @php $running = 0; @endphp

        @forelse($rows as $r)
            @php
                $running += ((float)$r['debit'] - (float)$r['credit']);
                $party = $r['party_type'] && $r['party_type'] !== 'NONE'
                    ? ($r['party_type'].' #'.$r['party_id'])
                    : '—';
            @endphp
            <tr>
                <td class="p-3">{{ \Illuminate\Support\Carbon::parse($r['posting_date'])->format('Y-m-d') }}</td>
                <td class="p-3">
                    <div class="font-mono text-xs text-slate-400">{{ $r['code'] }}</div>
                    <div>{{ $r['name'] }}</div>
                </td>
                <td class="p-3 text-right">{{ number_format((float)$r['debit'],2) }}</td>
                <td class="p-3 text-right">{{ number_format((float)$r['credit'],2) }}</td>
                <td class="p-3">{{ $r['currency'] }}</td>
                <td class="p-3">
                    @if($r['journal_entry_id'])
                        <a class="text-indigo-300 hover:underline"
                           href="{{ route('modules.accounting.journals.show', $r['journal_entry_id']) }}">
                            JE #{{ $r['journal_entry_id'] }}
                        </a>
                    @else
                        —
                    @endif
                </td>
                <td class="p-3">{{ $party }}</td>
            </tr>
        @empty
            <tr>
                <td class="p-3 text-slate-400" colspan="7">No ledger entries found for this range.</td>
            </tr>
        @endforelse
        </tbody>

        @if(count($rows))
        <tfoot class="bg-white/5">
        <tr>
            <th class="p-3 text-left" colspan="2">Running Net (Dr - Cr)</th>
            <th class="p-3 text-right" colspan="5">{{ number_format((float)$running,2) }}</th>
        </tr>
        </tfoot>
        @endif
    </table>
</div>
@endsection
