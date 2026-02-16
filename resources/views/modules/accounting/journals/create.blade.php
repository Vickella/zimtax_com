@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-white">New Journal Entry</h1>
            <p class="text-sm text-white/70">Debits must equal credits. Posting will generate GL entries.</p>
        </div>

        <a href="{{ route('modules.accounting.journals.index') }}"
           class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 text-white text-sm">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 p-4 text-white">
            <div class="font-semibold mb-2">Fix the following:</div>
            <ul class="list-disc pl-6 space-y-1 text-white/90">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('modules.accounting.journals.store') }}" id="jeForm">
        @csrf

        {{-- IMPORTANT: add padding-bottom so content never hides behind sticky footer --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 pb-28">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm text-white/80 mb-1">Posting Date</label>
                    <input type="date"
                           name="posting_date"
                           value="{{ old('posting_date', now()->toDateString()) }}"
                           class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block text-sm text-white/80 mb-1">Memo</label>
                    <input type="text"
                           name="memo"
                           value="{{ old('memo') }}"
                           placeholder="e.g. January payroll accrual"
                           class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block text-sm text-white/80 mb-1">Currency <span class="text-red-400">*</span></label>
                    <select name="currency" id="currency"
                            class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(($currencies ?? []) as $c)
                            <option value="{{ $c->code }}" @selected(old('currency', $baseCurrency ?? 'USD') === $c->code)>
                                {{ $c->code }} — {{ $c->name ?? $c->code }}
                            </option>
                        @endforeach

                        @if(empty($currencies))
                            <option value="{{ old('currency', $baseCurrency ?? 'USD') }}" selected>
                                {{ old('currency', $baseCurrency ?? 'USD') }}
                            </option>
                        @endif
                    </select>
                    <div class="text-xs text-white/60 mt-1">
                        Base: {{ $baseCurrency ?? 'USD' }} (auto 1.0000 when same currency)
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-white/80 mb-1">Exchange Rate <span class="text-red-400">*</span></label>
                    <input type="number"
                           step="0.00000001"
                           min="0.00000001"
                           name="exchange_rate"
                           id="exchange_rate"
                           value="{{ old('exchange_rate', 1) }}"
                           class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-black/10 overflow-hidden">
                <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between gap-3">
                    <div class="text-white font-semibold">Lines</div>

                    <div class="flex items-center gap-3 text-sm text-white/80">
                        <div>Debit: <span id="debitTotal" class="font-semibold text-white">0.00</span></div>
                        <div>Credit: <span id="creditTotal" class="font-semibold text-white">0.00</span></div>
                        <div class="px-2 py-1 rounded-lg bg-white/10">
                            Diff: <span id="diffTotal" class="font-semibold text-white">0.00</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-white/5">
                        <tr class="text-left text-xs text-white/70">
                            <th class="px-4 py-3 w-[320px]">Account</th>
                            <th class="px-4 py-3 w-[260px]">Description</th>
                            <th class="px-4 py-3 w-[160px]">Debit</th>
                            <th class="px-4 py-3 w-[160px]">Credit</th>
                            <th class="px-4 py-3 w-[220px]">Party</th>
                            <th class="px-4 py-3 w-[120px] text-right">Remove</th>
                        </tr>
                        </thead>

                        <tbody id="linesBody" class="divide-y divide-white/10">
                        {{-- Seed 2 rows by default (or old input) --}}
                        @php
                            $oldLines = old('lines', []);
                            if (empty($oldLines)) {
                                $oldLines = [
                                    ['account_id'=>'','description'=>'','debit'=>0,'credit'=>0,'party_type'=>'NONE','party_id'=>''],
                                    ['account_id'=>'','description'=>'','debit'=>0,'credit'=>0,'party_type'=>'NONE','party_id'=>''],
                                ];
                            }
                        @endphp

                        @foreach($oldLines as $i => $l)
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <select name="lines[{{ $i }}][account_id]"
                                            class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2">
                                        <option value="">Select account…</option>
                                        @foreach(($accounts ?? []) as $acc)
                                            <option value="{{ $acc->id }}" @selected(($l['account_id'] ?? '') == $acc->id)>
                                                {{ $acc->code }} — {{ $acc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="px-4 py-3">
                                    <input type="text"
                                           name="lines[{{ $i }}][description]"
                                           value="{{ $l['description'] ?? '' }}"
                                           class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2"
                                           placeholder="Narration" />
                                </td>

                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0"
                                           name="lines[{{ $i }}][debit]"
                                           value="{{ $l['debit'] ?? 0 }}"
                                           class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 debitInput" />
                                </td>

                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0"
                                           name="lines[{{ $i }}][credit]"
                                           value="{{ $l['credit'] ?? 0 }}"
                                           class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 creditInput" />
                                </td>

                                <td class="px-4 py-3">
                                    <select name="lines[{{ $i }}][party_type]"
                                            class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 partyType">
                                        @php $pt = $l['party_type'] ?? 'NONE'; @endphp
                                        <option value="NONE" @selected($pt==='NONE')>None</option>
                                        <option value="CUSTOMER" @selected($pt==='CUSTOMER')>Customer</option>
                                        <option value="SUPPLIER" @selected($pt==='SUPPLIER')>Supplier</option>
                                        <option value="EMPLOYEE" @selected($pt==='EMPLOYEE')>Employee</option>
                                    </select>

                                    <input type="text"
                                           name="lines[{{ $i }}][party_id]"
                                           value="{{ $l['party_id'] ?? '' }}"
                                           class="mt-2 w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 partyId"
                                           placeholder="Party ID (optional)" />
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <button type="button"
                                            class="px-3 py-2 rounded-xl bg-red-500/15 hover:bg-red-500/25 text-red-200 text-sm removeRowBtn">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-white/10 flex items-center justify-between">
                    <button type="button"
                            id="addLineBtn"
                            class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-sm">
                        + Add Line
                    </button>

                    <div class="text-xs text-white/60">
                        Tip: put values in either Debit OR Credit (not both) per line.
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky footer action bar: will never disappear --}}
        <div class="fixed bottom-0 left-0 right-0 z-50">
            <div class="max-w-6xl mx-auto px-4 pb-4">
                <div class="rounded-2xl border border-white/10 bg-black/50 backdrop-blur px-4 py-3 flex items-center justify-between">
                    <div class="text-sm text-white/70">
                        Ensure totals balance before posting.
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('modules.accounting.journals.index') }}"
                           class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-sm">
                            Cancel
                        </a>

                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold"
                                id="saveBtn">
                            Save Journal (Draft)
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
(function () {
    const linesBody = document.getElementById('linesBody');
    const addLineBtn = document.getElementById('addLineBtn');

    function nextIndex() {
        const rows = linesBody.querySelectorAll('tr');
        return rows.length;
    }

    function recalcTotals() {
        let d = 0, c = 0;

        document.querySelectorAll('.debitInput').forEach(i => d += parseFloat(i.value || 0));
        document.querySelectorAll('.creditInput').forEach(i => c += parseFloat(i.value || 0));

        const diff = (d - c);

        document.getElementById('debitTotal').textContent = d.toFixed(2);
        document.getElementById('creditTotal').textContent = c.toFixed(2);
        document.getElementById('diffTotal').textContent = diff.toFixed(2);

        // Optional: disable save if not balanced
        const saveBtn = document.getElementById('saveBtn');
        if (Math.abs(diff) > 0.009) {
            saveBtn.disabled = true;
            saveBtn.classList.add('opacity-60','cursor-not-allowed');
        } else {
            saveBtn.disabled = false;
            saveBtn.classList.remove('opacity-60','cursor-not-allowed');
        }
    }

    function bindRow(row) {
        const removeBtn = row.querySelector('.removeRowBtn');
        removeBtn.addEventListener('click', () => {
            // keep at least 2 lines
            const rows = linesBody.querySelectorAll('tr');
            if (rows.length <= 2) return;

            row.remove();
            normalizeIndexes();
            recalcTotals();
        });

        row.querySelectorAll('input').forEach(inp => {
            inp.addEventListener('input', recalcTotals);
        });
    }

    function normalizeIndexes() {
        const rows = linesBody.querySelectorAll('tr');
        rows.forEach((row, idx) => {
            row.querySelectorAll('select, input').forEach(el => {
                if (!el.name) return;
                el.name = el.name.replace(/lines\[\d+\]/, `lines[${idx}]`);
            });
        });
    }

    addLineBtn.addEventListener('click', () => {
        const idx = nextIndex();

        const tr = document.createElement('tr');
        tr.className = 'align-top';
        tr.innerHTML = `
            <td class="px-4 py-3">
                <select name="lines[${idx}][account_id]" class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2">
                    <option value="">Select account…</option>
                    @foreach(($accounts ?? []) as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="px-4 py-3">
                <input type="text" name="lines[${idx}][description]" value="" class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2" placeholder="Narration" />
            </td>
            <td class="px-4 py-3">
                <input type="number" step="0.01" min="0" name="lines[${idx}][debit]" value="0" class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 debitInput" />
            </td>
            <td class="px-4 py-3">
                <input type="number" step="0.01" min="0" name="lines[${idx}][credit]" value="0" class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 creditInput" />
            </td>
            <td class="px-4 py-3">
                <select name="lines[${idx}][party_type]" class="w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 partyType">
                    <option value="NONE" selected>None</option>
                    <option value="CUSTOMER">Customer</option>
                    <option value="SUPPLIER">Supplier</option>
                    <option value="EMPLOYEE">Employee</option>
                </select>
                <input type="text" name="lines[${idx}][party_id]" value="" class="mt-2 w-full rounded-xl bg-black/20 border border-white/10 text-white px-3 py-2 partyId" placeholder="Party ID (optional)" />
            </td>
            <td class="px-4 py-3 text-right">
                <button type="button" class="px-3 py-2 rounded-xl bg-red-500/15 hover:bg-red-500/25 text-red-200 text-sm removeRowBtn">Remove</button>
            </td>
        `;

        linesBody.appendChild(tr);
        bindRow(tr);
        recalcTotals();

        // smooth scroll into view so user sees the new row
        tr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // bind existing rows
    linesBody.querySelectorAll('tr').forEach(bindRow);

    // initial totals
    recalcTotals();
})();
</script>
@endsection
