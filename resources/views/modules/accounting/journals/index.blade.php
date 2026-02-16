@extends('layouts.app')

@section('page_title','Journal Entries')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div class="text-sm text-slate-300">Drafts, posted entries, reversals</div>
    <a href="{{ route('modules.accounting.journals.create') }}"
       class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
        + New Journal
    </a>
</div>

<div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
    <table class="w-full text-sm">
        <thead class="bg-white/5">
        <tr>
            <th class="p-3 text-left">Entry No</th>
            <th class="p-3 text-left">Posting Date</th>
            <th class="p-3 text-left">Memo</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3"></th>
        </tr>
        </thead>
        <tbody class="divide-y divide-white/10">
        @foreach($journals as $j)
            <tr>
                <td class="p-3 font-mono">{{ $j->entry_no }}</td>
                <td class="p-3">{{ $j->posting_date?->format('Y-m-d') }}</td>
                <td class="p-3">{{ $j->memo }}</td>
                <td class="p-3">{{ $j->status }}</td>
                <td class="p-3 text-right">
                    <a class="text-indigo-300 hover:underline" href="{{ route('modules.accounting.journals.show',$j) }}">Open</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $journals->links() }}</div>
@endsection
