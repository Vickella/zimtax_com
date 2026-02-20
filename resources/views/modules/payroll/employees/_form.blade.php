@php
    $isEdit = isset($employee) && $employee?->id;

    $earnRows = old('earnings') ?? collect($earnings ?? [])->map(function ($row) {
        return [
            'component_id' => is_array($row) ? ($row['component_id'] ?? null) : ($row->payroll_component_id ?? $row->component_id ?? null),
            'amount'       => is_array($row) ? ($row['amount'] ?? null) : ($row->amount ?? null),
        ];
    })->values()->all();

    $dedRows = old('deductions') ?? collect($deductions ?? [])->map(function ($row) {
        return [
            'component_id' => is_array($row) ? ($row['component_id'] ?? null) : ($row->payroll_component_id ?? $row->component_id ?? null),
            'amount'       => is_array($row) ? ($row['amount'] ?? null) : ($row->amount ?? null),
        ];
    })->values()->all();
@endphp

{{-- ✅ This wrapper must sit inside a parent that is flex + has height.
     Your create/edit blade must have: class="flex flex-col h-[calc(100vh-120px)]" etc --}}
<div class="space-y-4">

    {{-- SECTION 1 --}}
    <div class="rounded-2xl ring-1 ring-white/10 bg-black/10 p-4">
        <div class="mb-3">
            <div class="text-sm font-semibold">Personal Details</div>
            <div class="text-xs text-slate-300">Employee identity, employment, and payment information.</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-xs text-slate-300">Employee No</label>
                <input name="employee_no" value="{{ old('employee_no', $employee->employee_no ?? '') }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm" required>
            </div>

            <div>
                <label class="text-xs text-slate-300">First Name</label>
                <input name="first_name" value="{{ old('first_name', $employee->first_name ?? '') }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm" required>
            </div>

            <div>
                <label class="text-xs text-slate-300">Last Name</label>
                <input name="last_name" value="{{ old('last_name', $employee->last_name ?? '') }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm" required>
            </div>

            <div>
                <label class="text-xs text-slate-300">National ID</label>
                <input name="national_id" value="{{ old('national_id', $employee->national_id ?? '') }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-slate-300">TIN (ZIMRA)</label>
                <input name="tin" value="{{ old('tin', $employee->tin ?? '') }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-slate-300">NSSA Number</label>
                <input name="nssa_number" value="{{ old('nssa_number', $employee->nssa_number ?? '') }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-slate-300">NEC</label>
                <input name="nec" value="{{ old('nec', $employee->nec ?? '') }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-slate-300">Hire Date</label>
                <input type="date" name="hire_date"
                       value="{{ old('hire_date', isset($employee->hire_date) ? \Carbon\Carbon::parse($employee->hire_date)->format('Y-m-d') : '') }}"
                       class="mt-1 w-full rounded-lg bg-white/5 ring-1 ring-white/10 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-slate-300">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2 text-sm">
                    @php $status = old('status', $employee->status ?? 'ACTIVE'); @endphp
                    <option value="ACTIVE" @selected($status === 'ACTIVE')>ACTIVE</option>
                    <option value="INACTIVE" @selected($status === 'INACTIVE')>INACTIVE</option>
                </select>
            </div>
        </div>
    </div>

    {{-- SECTION 2 --}}
    <div class="rounded-2xl ring-1 ring-white/10 bg-black/10 p-4">
        <div class="mb-3">
            <div class="text-sm font-semibold">Salary Structure</div>
            <div class="text-xs text-slate-300">Earnings and deductions applied during payroll runs.</div>
        </div>

        {{-- ✅ Make tables area scrollable if too many rows --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Earnings --}}
            <div class="rounded-xl ring-1 ring-white/10 bg-white/5 overflow-hidden">
                <div class="p-3 border-b border-white/10 flex items-center justify-between">
                    <div class="text-sm font-semibold">Earnings</div>
                    <button type="button" id="add-earning"
                            class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                        Add Line
                    </button>
                </div>

                <div class="max-h-[320px] overflow-y-auto overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs text-slate-300 sticky top-0 bg-slate-900/70 backdrop-blur">
                            <tr class="border-b border-white/10">
                                <th class="text-left px-3 py-2 w-[60%]">Component</th>
                                <th class="text-right px-3 py-2 w-[25%]">Amount</th>
                                <th class="px-3 py-2 w-[15%]"></th>
                            </tr>
                        </thead>
                        <tbody id="earnings-body" class="divide-y divide-white/10">
                            @foreach($earnRows as $i => $row)
                                <tr>
                                    <td class="px-3 py-2">
                                        <select name="earnings[{{ $i }}][component_id]"
                                                class="w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2" required>
                                            <option value="">Select</option>
                                            @foreach($earningComponents as $c)
                                                <option value="{{ $c->id }}" @selected((string)($row['component_id'] ?? '') === (string)$c->id)>
                                                    {{ $c->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <input name="earnings[{{ $i }}][amount]" type="number" step="0.01" min="0"
                                               value="{{ $row['amount'] ?? '' }}"
                                               class="w-full text-right rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2" required>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <button type="button"
                                                class="remove-row text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                            @if(count($earnRows) === 0)
                                <tr class="hint-row">
                                    <td class="px-3 py-3 text-xs text-slate-300" colspan="3">
                                        No earnings lines yet. Click <span class="text-slate-200">Add Line</span>.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Deductions --}}
            <div class="rounded-xl ring-1 ring-white/10 bg-white/5 overflow-hidden">
                <div class="p-3 border-b border-white/10 flex items-center justify-between">
                    <div class="text-sm font-semibold">Deductions</div>
                    <button type="button" id="add-deduction"
                            class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                        Add Line
                    </button>
                </div>

                <div class="max-h-[320px] overflow-y-auto overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs text-slate-300 sticky top-0 bg-slate-900/70 backdrop-blur">
                            <tr class="border-b border-white/10">
                                <th class="text-left px-3 py-2 w-[60%]">Component</th>
                                <th class="text-right px-3 py-2 w-[25%]">Amount</th>
                                <th class="px-3 py-2 w-[15%]"></th>
                            </tr>
                        </thead>
                        <tbody id="deductions-body" class="divide-y divide-white/10">
                            @foreach($dedRows as $i => $row)
                                <tr>
                                    <td class="px-3 py-2">
                                        <select name="deductions[{{ $i }}][component_id]"
                                                class="w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2" required>
                                            <option value="">Select</option>
                                            @foreach($deductionComponents as $c)
                                                <option value="{{ $c->id }}" @selected((string)($row['component_id'] ?? '') === (string)$c->id)>
                                                    {{ $c->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <input name="deductions[{{ $i }}][amount]" type="number" step="0.01" min="0"
                                               value="{{ $row['amount'] ?? '' }}"
                                               class="w-full text-right rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2" required>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <button type="button"
                                                class="remove-row text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                            @if(count($dedRows) === 0)
                                <tr class="hint-row">
                                    <td class="px-3 py-3 text-xs text-slate-300" colspan="3">
                                        No deduction lines yet. Click <span class="text-slate-200">Add Line</span>.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- ✅ JS inside the partial, NO stacks, NO yield required --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const earningBody = document.getElementById('earnings-body');
    const deductionBody = document.getElementById('deductions-body');
    const addEarningBtn = document.getElementById('add-earning');
    const addDeductionBtn = document.getElementById('add-deduction');

    if (!earningBody || !deductionBody || !addEarningBtn || !addDeductionBtn) return;

    const earningOptions = `{!! collect($earningComponents)->map(fn($c) => "<option value='{$c->id}'>".e($c->name)."</option>")->implode('') !!}`;
    const deductionOptions = `{!! collect($deductionComponents)->map(fn($c) => "<option value='{$c->id}'>".e($c->name)."</option>")->implode('') !!}`;

    function rowCount(tbody) {
        return [...tbody.querySelectorAll('tr')].filter(tr => tr.querySelector('select')).length;
    }

    function removeHint(tbody){
        tbody.querySelectorAll('.hint-row').forEach(r => r.remove());
    }

    function rowHtml(type, idx, options) {
        return `
        <tr>
            <td class="px-3 py-2">
                <select name="${type}[${idx}][component_id]" class="w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2" required>
                    <option value="">Select</option>
                    ${options}
                </select>
            </td>
            <td class="px-3 py-2 text-right">
                <input name="${type}[${idx}][amount]" type="number" step="0.01" min="0"
                       class="w-full text-right rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2" required>
            </td>
            <td class="px-3 py-2 text-right">
                <button type="button" class="remove-row text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">Remove</button>
            </td>
        </tr>`;
    }

    addEarningBtn.addEventListener('click', () => {
        removeHint(earningBody);
        const idx = rowCount(earningBody);
        earningBody.insertAdjacentHTML('beforeend', rowHtml('earnings', idx, earningOptions));
    });

    addDeductionBtn.addEventListener('click', () => {
        removeHint(deductionBody);
        const idx = rowCount(deductionBody);
        deductionBody.insertAdjacentHTML('beforeend', rowHtml('deductions', idx, deductionOptions));
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;
        btn.closest('tr')?.remove();
    });
});
</script>
