@extends('layouts.erp')
@section('page_title','Receipts')

@section('content')
<div class="h-full overflow-auto space-y-4">

    @if(session('ok'))
        <div class="p-3 rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20 text-emerald-200 text-sm">
            {{ session('ok') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3 rounded-lg bg-red-500/10 ring-1 ring-red-500/20 text-red-200 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-3">
        <div class="text-sm text-slate-300">Customer receipts posted to cash/bank and AR.</div>
        <a href="{{ route('modules.sales.receipts.create') }}"
           class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
            + New Receipt
        </a>
    </div>

    <div class="rounded-xl ring-1 ring-white/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-white/5">
                <tr>
                    <th class="p-3 text-left">Receipt No</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Customer</th>
                    <th class="p-3 text-left">Currency</th>
                    <th class="p-3 text-left">Amount</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($receipts as $r)
                    <tr class="hover:bg-white/5">
                        <td class="p-3">
                            <a class="text-indigo-200 hover:underline"
                               href="{{ route('modules.sales.receipts.show', $r) }}">
                                {{ $r->payment_no }}
                            </a>
                        </td>
                        <td class="p-3">{{ $r->posting_date }}</td>
                        <td class="p-3">{{ $r->party_id }}</td>
                        <td class="p-3">{{ $r->currency }}</td>
                        <td class="p-3">{{ number_format((float)$r->amount, 2) }}</td>
                        <td class="p-3">{{ $r->status }}</td>
                        <td class="p-3 text-right">
                            <a class="text-slate-200 hover:underline"
                               href="{{ route('modules.sales.receipts.show', $r) }}">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-4 text-slate-300" colspan="7">No receipts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $receipts->links() }}</div>

</div>
@endsection
