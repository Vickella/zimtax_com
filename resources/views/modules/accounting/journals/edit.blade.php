@extends('layouts.app')

@section('content')
@php
    $oldLines = old('lines');
    $lines = $oldLines ?: $journal->lines->map(function($l){
        return [
            'account_id' => $l->account_id,
            'description' => $l->description,
            'debit' => $l->debit,
            'credit' => $l->credit,
            'party_type' => $l->party_type ?? 'NONE',
            'party_id' => $l->party_id ?? '',
        ];
    })->toArray();

    if (count($lines) < 1) {
        $lines = [['account_id'=>'','debit'=>'0','credit'=>'0','description'=>'','party_type'=>'NONE','party_id'=>'']];
    }
@endphp

<div class="max-w-6xl mx-auto p-4 md:p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl md:text-2xl font-semibold text-slate-100">Edit Journal {{ $journal->entry_no }}</h1>
            <p class="text-sm text-slate-400">Only DRAFT journals can be edited.</p>
        </div>

        <a href="{{ route('modules.accounting.journals.show', $journal) }}"
           class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 text-slate-200 text-sm">
            Back
        </a>
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

    <form method="POST" action="{{ route('modules.accounting.journals.update', $journal) }}"
          class="rounded-xl ring-1 ring-white/10 bg-black/20 p-4 space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-xs text-slate-300">Posting Date</label>
                <input type="date" name="posting_date" value="{{ old('posting_date', \Illuminate\Support\Carbon::parse($journal->posting_date)->toDateString()) }}"
                       class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none"
                       required>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs text-slate-300">Memo</label>
                <input name="memo" value="{{ old('memo', $journal->memo) }}"
                       class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none"
                       required>
            </div>
        </div>

        <div class="rounded-xl ring-1 ring-white/10 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="p-3 text-left text-slate-300">Account</th>
                        <th class="p-3 text-left text-slate-300">Description</th>
                        <th class="p-3 text-right text-slate-300">Debit</th>
                        <th class="p-3 text-right text-slate-300">Credit</th>
                        <th class="p-3 text-left text-slate-300">Party</th>
                    </tr>
                </thead>

                <tbody id="jl-body" class="divide-y divide-white/10">
                    @foreach($lines as $i => $l)
                        <tr>
                            <td class="p-2 w-64">
                                <select name="lines[{{ $i }}][account_id]"
                                        class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none" required>
                                    <option value="">Select...</option>
                                    @foreach($accounts as $a)
                                        <option value="{{ $a->id }}" @selected((string)($l['account_id'] ?? '') === (string)$a->id)>
                                            {{ $a->code }} — {{ $a->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td class="p-2">
                                <input name="lines[{{ $i }}][description]" value="{{ $l['description'] ?? '' }}"
                                       class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none">
                            </td>

                            <td class="p-2 w-40">
                                <input name="lines[{{ $i }}][debit]" value="{{ $l['debit'] ?? '0' }}"
                                       inputmode="decimal"
                                       class="w-full px-2 py-2 text-right rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none">
                            </td>

                            <td class="p-2 w-40">
                                <input name="lines[{{ $i }}][credit]" value="{{ $l['credit'] ?? '0' }}"
                                       inputmode="decimal"
                                       class="w-full px-2 py-2 text-right rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none">
                            </td>

                            <td class="p-2 w-56">
                                <select name="lines[{{ $i }}][party_type]"
                                        class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none">
                                    <option value="NONE" @selected(($l['party_type'] ?? 'NONE') === 'NONE')>None</option>
                                    <option value="CUSTOMER" @selected(($l['party_type'] ?? '') === 'CUSTOMER')>Customer</option>
                                    <option value="SUPPLIER" @selected(($l['party_type'] ?? '') === 'SUPPLIER')>Supplier</option>
                                    <option value="EMPLOYEE" @selected(($l['party_type'] ?? '') === 'EMPLOYEE')>Employee</option>
                                </select>
                                <input name="lines[{{ $i }}][party_id]" value="{{ $l['party_id'] ?? '' }}"
                                       class="w-full mt-1 px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none"
                                       placeholder="Party ID (optional)">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2">
            <button type="button"
                    class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 text-slate-200 text-sm"
                    onclick="addJLine()">
                + Add Line
            </button>

            <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm">
                Update Journal
            </button>
        </div>
    </form>

</div>

<script>
let jlIndex = {{ count($lines) }};

function addJLine() {
    const tbody = document.getElementById('jl-body');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="p-2 w-64">
            <select name="lines[${jlIndex}][account_id]" class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none" required>
                <option value="">Select...</option>
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>
                @endforeach
            </select>
        </td>
        <td class="p-2">
            <input name="lines[${jlIndex}][description]" class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none" placeholder="Line memo">
        </td>
        <td class="p-2 w-40">
            <input name="lines[${jlIndex}][debit]" value="0" inputmode="decimal" class="w-full px-2 py-2 text-right rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none">
        </td>
        <td class="p-2 w-40">
            <input name="lines[${jlIndex}][credit]" value="0" inputmode="decimal" class="w-full px-2 py-2 text-right rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none">
        </td>
        <td class="p-2 w-56">
            <select name="lines[${jlIndex}][party_type]" class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none">
                <option value="NONE">None</option>
                <option value="CUSTOMER">Customer</option>
                <option value="SUPPLIER">Supplier</option>
                <option value="EMPLOYEE">Employee</option>
            </select>
            <input name="lines[${jlIndex}][party_id]" class="w-full mt-1 px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 outline-none" placeholder="Party ID (optional)">
        </td>
    `;
    tbody.appendChild(row);
    jlIndex++;
}
</script>
@endsection
