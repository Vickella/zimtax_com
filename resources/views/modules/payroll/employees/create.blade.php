@extends('layouts.app')

@section('page_title','New Employee')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4">

    {{-- FULL HEIGHT CARD --}}
    <div class="rounded-2xl ring-1 ring-white/10 bg-slate-950/40 overflow-hidden flex flex-col"
         style="height: calc(100vh - 120px);">

        {{-- Header --}}
        <div class="p-4 border-b border-white/10 flex items-start justify-between gap-3">
            <div>
                <div class="text-base font-semibold">New Employee</div>
                <div class="text-xs text-slate-300">Capture personal details and salary structure.</div>
            </div>

            <a href="{{ route('modules.payroll.employees.index') }}"
               class="shrink-0 text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">
                ← Employees
            </a>
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="m-4 rounded-xl bg-rose-500/10 ring-1 ring-rose-500/20 p-3 text-xs text-rose-200">
                Please fix the highlighted fields and try again.
            </div>
        @endif

        <form method="POST"
              action="{{ route('modules.payroll.employees.store') }}"
              class="flex-1 flex flex-col min-h-0">
            @csrf

            {{-- ✅ SCROLLABLE BODY --}}
            <div class="flex-1 min-h-0 overflow-y-auto p-4">
                @include('modules.payroll.employees._form', [
                    'employee' => null,
                    'earnings' => [],
                    'deductions' => [],
                    'earningComponents' => $earningComponents ?? [],
                    'deductionComponents' => $deductionComponents ?? [],
                ])
            </div>

            {{-- ✅ STICKY FOOTER --}}
            <div class="shrink-0 bg-slate-950/80 backdrop-blur border-t border-white/10 p-3">
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('modules.payroll.employees.index') }}"
                       class="text-xs rounded-lg px-3 py-2 bg-white/5 hover:bg-white/10 ring-1 ring-white/10">
                        Cancel
                    </a>
                    <button type="submit"
                            class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                        Save Employee
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const earningBody = document.getElementById('earnings-body');
    const deductionBody = document.getElementById('deductions-body');
    const addEarningBtn = document.getElementById('add-earning');
    const addDeductionBtn = document.getElementById('add-deduction');

    if (!earningBody || !deductionBody || !addEarningBtn || !addDeductionBtn) {
        console.warn('Payroll form elements missing. Check IDs: earnings-body, deductions-body, add-earning, add-deduction');
        return;
    }

    function dataRowCount(tbody){
        return Array.from(tbody.querySelectorAll('tr')).filter(tr => tr.querySelector('select')).length;
    }

    function removeHintRow(tbody){
        tbody.querySelectorAll('.hint-row').forEach(r => r.remove());
    }

    function ensureHintRow(tbody, msg){
        if (tbody.querySelectorAll('select').length > 0) return;
        tbody.insertAdjacentHTML('beforeend', `
            <tr class="hint-row">
                <td class="px-3 py-3 text-xs text-slate-300" colspan="3">${msg}</td>
            </tr>
        `);
    }

    function rowHtml(type, index, optionsHtml) {
        return `
            <tr>
                <td class="px-3 py-2">
                    <select name="${type}[${index}][component_id]"
                            class="w-full rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2" required>
                        <option value="">Select</option>
                        ${optionsHtml}
                    </select>
                </td>
                <td class="px-3 py-2 text-right">
                    <input name="${type}[${index}][amount]" type="number" step="0.01" min="0"
                           class="w-full text-right rounded-lg bg-black/20 ring-1 ring-white/10 px-3 py-2" required>
                </td>
                <td class="px-3 py-2 text-right">
                    <button type="button"
                            class="remove-row text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                        Remove
                    </button>
                </td>
            </tr>
        `;
    }

    const earningOptions = `{!! collect($earningComponents ?? [])->map(fn($c) => "<option value='{$c->id}'>".e($c->name)."</option>")->implode('') !!}`;
    const deductionOptions = `{!! collect($deductionComponents ?? [])->map(fn($c) => "<option value='{$c->id}'>".e($c->name)."</option>")->implode('') !!}`;

    addEarningBtn.addEventListener('click', () => {
        removeHintRow(earningBody);
        const idx = dataRowCount(earningBody);
        earningBody.insertAdjacentHTML('beforeend', rowHtml('earnings', idx, earningOptions));
    });

    addDeductionBtn.addEventListener('click', () => {
        removeHintRow(deductionBody);
        const idx = dataRowCount(deductionBody);
        deductionBody.insertAdjacentHTML('beforeend', rowHtml('deductions', idx, deductionOptions));
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;

        const tr = btn.closest('tr');
        const tbody = tr.closest('tbody');
        tr.remove();

        if (tbody === earningBody) {
            ensureHintRow(earningBody, `No earnings lines yet. Click <span class="text-slate-200">Add Line</span>.`);
        }
        if (tbody === deductionBody) {
            ensureHintRow(deductionBody, `No deduction lines yet. Click <span class="text-slate-200">Add Line</span>.`);
        }
    });
});
</script>
@endsection
