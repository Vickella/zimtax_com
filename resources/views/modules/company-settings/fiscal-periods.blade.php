@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] overflow-hidden px-6 py-5">
  <div class="flex items-center justify-between mb-4">
    <div class="text-white">
      <div class="text-lg font-semibold">Fiscal Periods</div>
      <div class="text-xs text-white/70">Locking controls reporting</div>
    </div>
    <a href="{{ route('modules.index', ['module' => 'company-settings']) }}" class="text-sm text-white/80 hover:text-white">Back</a>
  </div>

  @if(session('ok'))
    <div class="mb-4 rounded-lg bg-emerald-500/15 border border-emerald-500/20 text-emerald-100 px-4 py-2 text-sm">{{ session('ok') }}</div>
  @endif

  <div class="grid grid-cols-12 gap-4">
    <form method="POST" action="{{ route('modules.company-settings.fiscal-periods.store') }}" class="col-span-12 lg:col-span-4 rounded-xl border border-white/10 bg-white/5 p-4">
      @csrf
      <div class="text-sm text-white/80 mb-3">Add</div>

      <label class="text-xs text-white/70">Name</label>
      <input name="name" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="2026-01">

      <label class="mt-3 text-xs text-white/70">Type</label>
      <select name="period_type" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
        <option value="MONTH">MONTH</option>
        <option value="QUARTER">QUARTER</option>
        <option value="YEAR">YEAR</option>
      </select>

      <label class="mt-3 text-xs text-white/70">Start</label>
      <input type="date" name="start_date" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">

      <label class="mt-3 text-xs text-white/70">End</label>
      <input type="date" name="end_date" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">

      <div class="mt-4 flex justify-end">
        <button class="rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 px-4 py-2 text-sm text-white">Save</button>
      </div>
    </form>

    <div class="col-span-12 lg:col-span-8 rounded-xl border border-white/10 bg-white/5 overflow-hidden">
      <div class="px-4 py-3 border-b border-white/10 text-sm text-white/80">Latest 200</div>
      <div class="max-h-[calc(100vh-11rem)] overflow-auto">
        <table class="w-full text-sm text-white/85">
          <thead class="text-xs text-white/60">
            <tr class="border-b border-white/10">
              <th class="text-left px-4 py-2">Name</th>
              <th class="text-left px-4 py-2">Type</th>
              <th class="text-left px-4 py-2">Start</th>
              <th class="text-left px-4 py-2">End</th>
              <th class="text-left px-4 py-2">Closed</th>
              <th class="text-left px-4 py-2"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($periods as $p)
              <tr class="border-b border-white/5">
                <td class="px-4 py-2">{{ $p->name }}</td>
                <td class="px-4 py-2">{{ $p->period_type }}</td>
                <td class="px-4 py-2">{{ $p->start_date?->format('Y-m-d') }}</td>
                <td class="px-4 py-2">{{ $p->end_date?->format('Y-m-d') }}</td>
                <td class="px-4 py-2">{{ $p->is_closed ? 'Yes' : 'No' }}</td>
                <td class="px-4 py-2 text-right">
                  @if(!$p->is_closed)
                    <form method="POST" action="{{ route('modules.company-settings.fiscal-periods.close', ['id' => $p->id]) }}">
                      @csrf
                      <button class="text-xs text-white/70 hover:text-white">Close</button>
                    </form>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
