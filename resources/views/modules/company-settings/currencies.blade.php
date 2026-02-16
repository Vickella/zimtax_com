@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] overflow-hidden px-6 py-5">
  <div class="flex items-center justify-between mb-4">
    <div class="text-white">
      <div class="text-lg font-semibold">Currencies</div>
      <div class="text-xs text-white/70">Multi-currency enabled</div>
    </div>
    <a href="{{ route('modules.index', ['module' => 'company-settings']) }}" class="text-sm text-white/80 hover:text-white">Back</a>
  </div>

  @if(session('ok'))
    <div class="mb-4 rounded-lg bg-emerald-500/15 border border-emerald-500/20 text-emerald-100 px-4 py-2 text-sm">{{ session('ok') }}</div>
  @endif

  <div class="grid grid-cols-12 gap-4">
    <form method="POST" action="{{ route('modules.company-settings.currencies.store') }}" class="col-span-12 lg:col-span-4 rounded-xl border border-white/10 bg-white/5 p-4">
      @csrf
      <div class="text-sm text-white/80 mb-3">Add / Update</div>

      <label class="text-xs text-white/70">Code (3)</label>
      <input name="code" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="USD">

      <label class="mt-3 text-xs text-white/70">Name</label>
      <input name="name" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="US Dollar">

      <label class="mt-3 text-xs text-white/70">Symbol</label>
      <input name="symbol" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm" placeholder="$">

      <label class="mt-3 inline-flex items-center gap-2 text-sm text-white/80">
        <input type="checkbox" name="is_active" value="1" checked> Active
      </label>

      <div class="mt-4 flex justify-end">
        <button class="rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 px-4 py-2 text-sm text-white">Save</button>
      </div>
    </form>

    <div class="col-span-12 lg:col-span-8 rounded-xl border border-white/10 bg-white/5 overflow-hidden">
      <div class="px-4 py-3 border-b border-white/10 text-sm text-white/80">List</div>
      <div class="max-h-[calc(100vh-11rem)] overflow-auto">
        <table class="w-full text-sm text-white/85">
          <thead class="text-xs text-white/60">
            <tr class="border-b border-white/10">
              <th class="text-left px-4 py-2">Code</th>
              <th class="text-left px-4 py-2">Name</th>
              <th class="text-left px-4 py-2">Symbol</th>
              <th class="text-left px-4 py-2">Active</th>
            </tr>
          </thead>
          <tbody>
            @foreach($currencies as $c)
              <tr class="border-b border-white/5">
                <td class="px-4 py-2">{{ $c->code }}</td>
                <td class="px-4 py-2">{{ $c->name }}</td>
                <td class="px-4 py-2">{{ $c->symbol }}</td>
                <td class="px-4 py-2">{{ $c->is_active ? 'Yes' : 'No' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
