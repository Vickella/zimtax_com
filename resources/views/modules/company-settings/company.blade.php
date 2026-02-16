@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] overflow-hidden px-6 py-5">
  <div class="flex items-center justify-between mb-4">
    <div class="text-white">
      <div class="text-lg font-semibold">Company</div>
      <div class="text-xs text-white/70">Single-company profile</div>
    </div>
    <a href="{{ route('modules.index', ['module' => 'company-settings']) }}" class="text-sm text-white/80 hover:text-white">Back</a>
  </div>

  @if(session('ok'))
    <div class="mb-4 rounded-lg bg-emerald-500/15 border border-emerald-500/20 text-emerald-100 px-4 py-2 text-sm">
      {{ session('ok') }}
    </div>
  @endif

  <form method="POST" action="{{ route('modules.company-settings.company.update') }}" class="rounded-xl border border-white/10 bg-white/5 p-4">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-12 gap-3">
      <div class="col-span-12 md:col-span-4">
        <label class="text-xs text-white/70">Code</label>
        <input name="code" value="{{ old('code', $company->code) }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
      </div>

      <div class="col-span-12 md:col-span-8">
        <label class="text-xs text-white/70">Name</label>
        <input name="name" value="{{ old('name', $company->name) }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
      </div>

      <div class="col-span-12 md:col-span-6">
        <label class="text-xs text-white/70">Trading Name</label>
        <input name="trading_name" value="{{ old('trading_name', $company->trading_name) }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
      </div>

      <div class="col-span-12 md:col-span-3">
        <label class="text-xs text-white/70">TIN</label>
        <input name="tin" value="{{ old('tin', $company->tin) }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
      </div>

      <div class="col-span-12 md:col-span-3">
        <label class="text-xs text-white/70">VAT Number</label>
        <input name="vat_number" value="{{ old('vat_number', $company->vat_number) }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
      </div>

      <div class="col-span-12">
        <label class="text-xs text-white/70">Address</label>
        <textarea name="address" rows="2" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">{{ old('address', $company->address) }}</textarea>
      </div>

      <div class="col-span-12 md:col-span-4">
        <label class="text-xs text-white/70">Phone</label>
        <input name="phone" value="{{ old('phone', $company->phone) }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
      </div>

      <div class="col-span-12 md:col-span-5">
        <label class="text-xs text-white/70">Email</label>
        <input name="email" value="{{ old('email', $company->email) }}" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
      </div>

      <div class="col-span-12 md:col-span-3">
        <label class="text-xs text-white/70">Base Currency</label>
        <select name="base_currency" class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
          @foreach($currencies as $c)
            <option value="{{ $c->code }}" @selected(old('base_currency', $company->base_currency) === $c->code)>{{ $c->code }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-span-12 md:col-span-3">
        <label class="text-xs text-white/70">Fiscal Start Month</label>
        <input type="number" min="1" max="12" name="fiscal_year_start_month" value="{{ old('fiscal_year_start_month', $company->fiscal_year_start_month) }}"
               class="mt-1 w-full rounded-lg bg-black/30 border border-white/10 text-white px-3 py-2 text-sm">
      </div>

      <div class="col-span-12 md:col-span-3 flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-white/80">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $company->is_active))>
          Active
        </label>
      </div>
    </div>

    <div class="mt-4 flex justify-end">
      <button class="rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 px-4 py-2 text-sm text-white">
        Save
      </button>
    </div>
  </form>
</div>
@endsection
