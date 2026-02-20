@extends('layouts.app')
@section('page_title','New Payroll Run')

@section('content')
<div class="h-full flex flex-col gap-4">

    <div>
        <h1 class="text-lg font-semibold">New Payroll Run</h1>
        <p class="text-xs text-slate-300">Select month and year. System creates the fiscal period if missing.</p>
    </div>

    <form method="POST" action="{{ route('modules.payroll.runs.store') }}"
          class="rounded-2xl ring-1 ring-white/10 bg-black/10 p-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-xs text-slate-300">Year</label>
                <input type="number" name="year" value="{{ old('year', now()->year) }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm" required>
            </div>

            <div>
                <label class="text-xs text-slate-300">Month</label>
                <select name="month"
                        class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm" required>
                    <option value="">Select month</option>
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" @selected(old('month', now()->month)==$m)>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="flex items-end">
                <button class="w-full text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                    Process Payroll
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
