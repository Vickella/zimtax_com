{{-- resources/views/modules/company-settings/payroll-statutory.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] overflow-hidden px-6 py-5">
  <div class="flex items-center justify-between mb-4">
    <div class="text-white">
      <div class="text-lg font-semibold">Payroll Statutory</div>
      <div class="text-xs text-white/70">NSSA, AIDS Levy, ZIMDEF (NEC stored in metadata)</div>
    </div>
    <a href="{{ route('modules.index', ['module' => 'company-settings']) }}" class="text-sm text-white/80 hover:text-white">Back</a>
  </div>

  @if(session('ok'))
    <div class="mb-4 rounded-lg bg-emerald-500/15 border border-emerald-500/20 text-emerald-100 px-4 py-2 text-sm">{{ session('ok') }}</div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg bg-rose-500/15 border border-rose-500/20 text-rose-100 px-4 py-2 text-sm">
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="grid grid-cols-12 gap-4">
    {{-- Add --}}
    <form method="POST" action="{{ route('modules.company-settings.payroll-statutory.store') }}" class="col-span-12 lg:col-span-4 rounded-xl border border-white/10 bg-white/5 p-4">
      @csrf
      <div class="text-sm text-white/80 mb-3">Add</div>

      <label class="text-xs text-white/70">Effective From</label>
      <input type="date" name="effective_from" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">

      <label class="mt-3 text-xs text-white/70">Effective To</label>
      <input type="date" name="effective_to" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">

      <div class="grid grid-cols-2 gap-3 mt-3">
        <div>
          <label class="text-xs text-white/70">NSSA Employee (%)</label>
          <input name="nssa_employee_rate" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="4.5">
        </div>
        <div>
          <label class="text-xs text-white/70">NSSA Employer (%)</label>
          <input name="nssa_employer_rate" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="4.5">
        </div>
      </div>

      <label class="mt-3 text-xs text-white/70">NSSA Ceiling Amount</label>
      <input name="nssa_ceiling_amount" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="0">

      <label class="mt-3 text-xs text-white/70">AIDS Levy Rate (%)</label>
      <input name="aids_levy_rate" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="3.0">

      <div class="grid grid-cols-2 gap-3 mt-3">
        <div>
          <label class="text-xs text-white/70">ZIMDEF Employee (%)</label>
          <input name="zimdef_employee_rate" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="">
        </div>
        <div>
          <label class="text-xs text-white/70">ZIMDEF Employer (%)</label>
          <input name="zimdef_employer_rate" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="">
        </div>
      </div>

      <label class="mt-3 text-xs text-white/70">NEC Rate (%)</label>
      <input name="nec_rate" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="1.5">

      <div class="mt-4 flex justify-end">
        <button class="rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 px-4 py-2 text-sm text-white">Save</button>
      </div>
    </form>

    {{-- List --}}
    <div class="col-span-12 lg:col-span-8 rounded-xl border border-white/10 bg-white/5 overflow-hidden">
      <div class="px-4 py-3 border-b border-white/10 text-sm text-white/80">List</div>

      <div class="max-h-[calc(100vh-11rem)] overflow-auto">
        <table class="w-full text-sm text-white/85">
          <thead class="text-xs text-white/60">
            <tr class="border-b border-white/10">
              <th class="text-left px-4 py-2">From</th>
              <th class="text-left px-4 py-2">To</th>
              <th class="text-left px-4 py-2">NSSA Emp</th>
              <th class="text-left px-4 py-2">NSSA Empr</th>
              <th class="text-left px-4 py-2">Ceiling</th>
              <th class="text-left px-4 py-2">AIDS</th>
              <th class="text-left px-4 py-2">NEC</th>
            </tr>
          </thead>
          <tbody>
            @foreach($settings as $s)
              @php
                $nec = is_array($s->metadata) ? ($s->metadata['nec_rate'] ?? null) : null;
              @endphp
              <tr class="border-b border-white/5">
                <td class="px-4 py-2">{{ $s->effective_from?->format('Y-m-d') }}</td>
                <td class="px-4 py-2">{{ $s->effective_to?->format('Y-m-d') ?? '-' }}</td>
                <td class="px-4 py-2">{{ $s->nssa_employee_rate }}</td>
                <td class="px-4 py-2">{{ $s->nssa_employer_rate }}</td>
                <td class="px-4 py-2">{{ $s->nssa_ceiling_amount }}</td>
                <td class="px-4 py-2">{{ $s->aids_levy_rate }}</td>
                <td class="px-4 py-2">{{ $nec ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
