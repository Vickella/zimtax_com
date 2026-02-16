{{-- resources/views/modules/company-settings/tax-rates.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] overflow-hidden px-6 py-5">
  <div class="flex items-center justify-between mb-4">
    <div class="text-white">
      <div class="text-lg font-semibold">Tax Rates</div>
      <div class="text-xs text-white/70">Effective-dated (VAT, Income Tax, etc.)</div>
    </div>
    <a href="{{ route('modules.index', ['module' => 'company-settings']) }}" class="text-sm text-white/80 hover:text-white">Back</a>
  </div>

  @if(session('ok'))
    <div class="mb-4 rounded-lg bg-emerald-500/15 border border-emerald-500/20 text-emerald-100 px-4 py-2 text-sm">
      {{ session('ok') }}
    </div>
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
    <form method="POST" action="{{ route('modules.company-settings.tax-rates.store') }}" class="col-span-12 lg:col-span-4 rounded-xl border border-white/10 bg-white/5 p-4">
      @csrf
      <div class="text-sm text-white/80 mb-3">Add</div>

      <label class="text-xs text-white/70">Tax Type</label>
      <select name="tax_type" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
        <option value="VAT">VAT</option>
        <option value="WHT">WHT</option>
        <option value="AIDS_LEVY">AIDS_LEVY</option>
        <option value="OTHER">OTHER</option>
      </select>

      <label class="mt-3 text-xs text-white/70">Code</label>
      <input name="code" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="VAT_STD">

      <label class="mt-3 text-xs text-white/70">Description</label>
      <input name="description" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="Standard VAT">

      <label class="mt-3 text-xs text-white/70">Rate (%)</label>
      <input name="rate" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="15.5">

      <div class="grid grid-cols-2 gap-3 mt-3">
        <div>
          <label class="text-xs text-white/70">Effective From</label>
          <input type="date" name="effective_from" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
        </div>
        <div>
          <label class="text-xs text-white/70">Effective To</label>
          <input type="date" name="effective_to" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
        </div>
      </div>

      <label class="mt-3 inline-flex items-center gap-2 text-sm text-white/80">
        <input type="checkbox" name="is_active" value="1" checked> Active
      </label>

      <div class="mt-4 flex justify-end">
        <button class="rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 px-4 py-2 text-sm text-white">Save</button>
      </div>
    </form>

    {{-- List (with inline edit) --}}
    <div class="col-span-12 lg:col-span-8 rounded-xl border border-white/10 bg-white/5 overflow-hidden">
      <div class="px-4 py-3 border-b border-white/10 text-sm text-white/80">List</div>

      <div class="max-h-[calc(100vh-11rem)] overflow-auto">
        <table class="w-full text-sm text-white/85">
          <thead class="text-xs text-white/60">
            <tr class="border-b border-white/10">
              <th class="text-left px-4 py-2">Type</th>
              <th class="text-left px-4 py-2">Code</th>
              <th class="text-left px-4 py-2">Rate</th>
              <th class="text-left px-4 py-2">From</th>
              <th class="text-left px-4 py-2">To</th>
              <th class="text-left px-4 py-2">Active</th>
              <th class="text-left px-4 py-2"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($rates as $r)
              <tr class="border-b border-white/5 align-top">
                <td class="px-4 py-2">{{ $r->tax_type }}</td>
                <td class="px-4 py-2">
                  <div class="font-medium">{{ $r->code }}</div>
                  @if($r->description)
                    <div class="text-xs text-white/60">{{ $r->description }}</div>
                  @endif
                </td>
                <td class="px-4 py-2">{{ $r->rate }}</td>
                <td class="px-4 py-2">{{ $r->effective_from?->format('Y-m-d') }}</td>
                <td class="px-4 py-2">{{ $r->effective_to?->format('Y-m-d') ?? '-' }}</td>
                <td class="px-4 py-2">{{ $r->is_active ? 'Yes' : 'No' }}</td>
                <td class="px-4 py-2 text-right">
                  <details class="inline-block">
                    <summary class="cursor-pointer text-xs text-white/70 hover:text-white">Edit</summary>
                    <div class="mt-2 w-[340px] rounded-lg border border-white/10 bg-black/40 p-3 text-left">
                      <form method="POST" action="{{ route('modules.company-settings.tax-rates.update', ['id' => $r->id]) }}">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="tax_type" value="{{ $r->tax_type }}">
                        <input type="hidden" name="code" value="{{ $r->code }}">

                        <label class="text-xs text-white/70">Description</label>
                        <input name="description" value="{{ $r->description }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">

                        <label class="mt-3 text-xs text-white/70">Rate (%)</label>
                        <input name="rate" value="{{ $r->rate }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">

                        <div class="grid grid-cols-2 gap-3 mt-3">
                          <div>
                            <label class="text-xs text-white/70">From</label>
                            <input type="date" name="effective_from" value="{{ optional($r->effective_from)->format('Y-m-d') }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
                          </div>
                          <div>
                            <label class="text-xs text-white/70">To</label>
                            <input type="date" name="effective_to" value="{{ optional($r->effective_to)->format('Y-m-d') }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
                          </div>
                        </div>

                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-white/80">
                          <input type="checkbox" name="is_active" value="1" @checked($r->is_active)> Active
                        </label>

                        <div class="mt-3 flex justify-end">
                          <button class="rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 px-3 py-2 text-sm text-white">Update</button>
                        </div>
                      </form>
                    </div>
                  </details>
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
