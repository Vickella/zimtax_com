@extends('layouts.app')
@section('page_title','Payroll Run')

@section('content')
<div class="h-full flex flex-col gap-4">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-semibold">Payroll Run</h1>
            <p class="text-xs text-slate-300">
                {{ $run->run_no }} • Period: {{ $period->name ?? $run->period_id }} • Status: {{ $run->status }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('modules.payroll.reports.nssa_p4.csv',$run) }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">
                NSSA P4 CSV
            </a>
            <a href="{{ route('modules.payroll.reports.zimra_itf16.csv',$run) }}"
               class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">
                ZIMRA ITF16 CSV
            </a>

            @if($run->status === 'DRAFT')
                <form method="POST" action="{{ route('modules.payroll.runs.submit',$run) }}">
                    @csrf
                    <button class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                        Submit & Post Journal
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="rounded-2xl ring-1 ring-white/10 bg-black/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-xs text-slate-300 border-b border-white/10">
                    <tr>
                        <th class="text-left px-3 py-2">Employee</th>
                        <th class="text-right px-3 py-2">Gross</th>
                        <th class="text-right px-3 py-2">PAYE</th>
                        <th class="text-right px-3 py-2">AIDS</th>
                        <th class="text-right px-3 py-2">NSSA</th>
                        <th class="text-right px-3 py-2">NEC</th>
                        <th class="text-right px-3 py-2">Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach($payslips as $ps)
                        <tr>
                            <td class="px-3 py-2">
                                {{ $ps->employee->last_name }}, {{ $ps->employee->first_name }}
                                <div class="text-xs text-slate-400">{{ $ps->employee->employee_no }}</div>
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format($ps->gross_pay,2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($ps->paye,2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($ps->aids_levy,2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($ps->nssa_employee,2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($ps->nec_levy ?? 0,2) }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format($ps->net_pay,2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
