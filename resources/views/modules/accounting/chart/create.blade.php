@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-4 md:p-6 space-y-6">

    <div>
        <h1 class="text-xl md:text-2xl font-semibold text-slate-100">New Account</h1>
        <p class="text-sm text-slate-400">Create a COA account used for postings.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-lg bg-rose-500/10 ring-1 ring-rose-500/20 text-rose-200 p-3 text-sm">
            <div class="font-medium mb-1">Fix the following:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('modules.accounting.chart.store') }}"
          class="rounded-xl ring-1 ring-white/10 bg-black/20 p-4 space-y-4">
        @csrf

        <div>
            <label class="text-xs text-slate-300">Code</label>
            <input name="code" value="{{ old('code') }}"
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none"
                   required>
        </div>

        <div>
            <label class="text-xs text-slate-300">Name</label>
            <input name="name" value="{{ old('name') }}"
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none"
                   required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="flex items-center gap-2 text-sm text-slate-200">
                <input type="checkbox" name="is_control_account" value="1" @checked(old('is_control_account') == 1)
                       class="rounded bg-black/30 ring-1 ring-white/10">
                Control Account (requires subledger)
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-200">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', 1) == 1)
                       class="rounded bg-black/30 ring-1 ring-white/10">
                Active
            </label>
        </div>

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('modules.accounting.chart.index') }}"
               class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 text-slate-200 text-sm">
                Cancel
            </a>
            <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm">
                Save Account
            </button>
        </div>
    </form>

</div>
@endsection
