@extends('layouts.app')

@section('page_title','Payroll Runs')

@section('content')
<div class="h-full flex flex-col gap-4">

    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold">Payroll Entry</h1>
            <p class="text-xs text-slate-300">
                Monthly payroll runs. Process payslips then submit to post journals.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('modules.payroll.index') }}"
               class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs bg-white/5 hover:bg-white/10 ring-1 ring-white/10">
                ← Payroll
            </a>
            <a href="{{ route('modules.payroll.runs.create') }}"
               class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                ➕ New Run
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-500/10 ring-1 ring-emerald-500/20 p-3 text-xs text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl ring-1 ring-white/10 bg-black/10 overflow-hidden">
        <div class="p-4 border-b border-white/10">
            <div class="text-sm font-semibold">Monthly Runs</div>
            <div class="text-xs text-slate-300">Open a run to review employees and payslips.</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-xs text-slate-300">
                    <tr class="border-b border-white/10">
                        <th class="text-left p-3">Run No</th>
                        <th class="text-left p-3">Period</th>
                        <th class="text-left p-3">Currency</th>
                        <th class="text-left p-3">Processed</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-right p-3">Action</th>
                    </tr>
                </thead>
                <tbody class="text-slate-200">
                    @forelse($runs as $r)
                        @php
                            $period = \DB::table('fiscal_periods')->where('id',$r->period_id)->first();
                        @endphp
                        <tr class="border-b border-white/5 hover:bg-white/5">
                            <td class="p-3 font-medium">{{ $r->run_no }}</td>
                            <td class="p-3 text-slate-300">{{ $period?->name ?? '—' }}</td>
                            <td class="p-3 text-slate-300">{{ $r->currency }}</td>
                            <td class="p-3 text-slate-300">{{ $r->processed_at ? $r->processed_at->format('Y-m-d H:i') : '—' }}</td>
                            <td class="p-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] ring-1 ring-white/10
                                    {{ $r->status === 'SUBMITTED' ? 'bg-emerald-500/10 text-emerald-200' : 'bg-white/5 text-slate-300' }}">
                                    {{ $r->status }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a class="text-xs underline underline-offset-2 hover:text-white"
                                   href="{{ route('modules.payroll.runs.show', $r->id) }}">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="p-4 text-slate-300" colspan="6">No payroll runs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $runs->links() }}
        </div>
    </div>

</div>
@endsection
