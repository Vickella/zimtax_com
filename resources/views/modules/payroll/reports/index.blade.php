@extends('layouts.app')
@section('page_title','Payroll Reports')

@section('content')
<div class="h-full flex flex-col gap-4">

    <div>
        <h1 class="text-lg font-semibold">Payroll Reports</h1>
        <p class="text-xs text-slate-300">Download statutory schedules for any submitted run.</p>
    </div>

    <div class="rounded-2xl ring-1 ring-white/10 bg-black/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-xs text-slate-300 border-b border-white/10">
                    <tr>
                        <th class="text-left px-3 py-2">Run</th>
                        <th class="text-left px-3 py-2">Status</th>
                        <th class="text-right px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach($runs as $r)
                        <tr>
                            <td class="px-3 py-2">{{ $r->run_no }}</td>
                            <td class="px-3 py-2">{{ $r->status }}</td>
                            <td class="px-3 py-2 text-right">
                                <a class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10"
                                   href="{{ route('modules.payroll.reports.nssa_p4.csv',$r) }}">
                                    NSSA P4
                                </a>
                                <a class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10"
                                   href="{{ route('modules.payroll.reports.zimra_itf16.csv',$r) }}">
                                    ZIMRA ITF16
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
