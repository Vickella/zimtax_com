@extends('layouts.app')
@section('page_title','Payroll')

@section('content')
<div class="h-full flex flex-col gap-4">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-semibold">Payroll</h1>
            <p class="text-xs text-slate-300">Employees, monthly payroll runs, statutory reports and downloads.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <a href="{{ route('modules.payroll.employees.index') }}"
           class="rounded-2xl ring-1 ring-white/10 bg-black/10 p-4 hover:bg-white/5 transition">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">Employees</div>
                <div class="text-xl">👤</div>
            </div>
            <div class="text-xs text-slate-300 mt-1">
                Capture employee details and salary structure (manual components only).
            </div>
        </a>

        <a href="{{ route('modules.payroll.runs.index') }}"
           class="rounded-2xl ring-1 ring-white/10 bg-black/10 p-4 hover:bg-white/5 transition">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">Payroll Entry</div>
                <div class="text-xl">🧾</div>
            </div>
            <div class="text-xs text-slate-300 mt-1">
                Run payroll monthly. Auto-calculates PAYE, NSSA, AIDS Levy, NEC.
            </div>
        </a>

        <a href="{{ route('modules.payroll.reports.index') }}"
           class="rounded-2xl ring-1 ring-white/10 bg-black/10 p-4 hover:bg-white/5 transition">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">Reports</div>
                <div class="text-xl">📊</div>
            </div>
            <div class="text-xs text-slate-300 mt-1">
                Download NSSA P4, ZIMRA ITF16 and other statutory schedules.
            </div>
        </a>

    </div>
</div>
@endsection
