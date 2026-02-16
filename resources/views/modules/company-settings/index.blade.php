@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-4rem)] overflow-hidden">
  <div class="px-6 py-5">
    <div class="flex items-center justify-between">
      <h1 class="text-lg font-semibold text-white">Company Settings</h1>
      <div class="text-xs text-white/70">ZimTax Compliance</div>
    </div>
  </div>

  <div class="px-6 pb-6 grid grid-cols-12 gap-4">
    <div class="col-span-12 lg:col-span-6">
      <div class="rounded-xl border border-white/10 bg-white/5">
        <div class="px-4 py-3 border-b border-white/10 text-sm text-white/80">Masters</div>
        <div class="p-3 grid grid-cols-2 gap-2">
          <a class="rounded-lg bg-white/5 hover:bg-white/10 px-3 py-2 text-sm text-white"
             href="{{ route('modules.company-settings.company.edit') }}">Company</a>
          <a class="rounded-lg bg-white/5 hover:bg-white/10 px-3 py-2 text-sm text-white"
             href="{{ route('modules.company-settings.currencies.index') }}">Currencies</a>
          <a class="rounded-lg bg-white/5 hover:bg-white/10 px-3 py-2 text-sm text-white"
             href="{{ route('modules.company-settings.exchange-rates.index') }}">Exchange Rates</a>
          <a class="rounded-lg bg-white/5 hover:bg-white/10 px-3 py-2 text-sm text-white"
             href="{{ route('modules.company-settings.fiscal-periods.index') }}">Fiscal Periods</a>
        </div>
      </div>
    </div>

    <div class="col-span-12 lg:col-span-6">
      <div class="rounded-xl border border-white/10 bg-white/5">
        <div class="px-4 py-3 border-b border-white/10 text-sm text-white/80">Tax & Statutory</div>
        <div class="p-3 grid grid-cols-2 gap-2">
          <a class="rounded-lg bg-white/5 hover:bg-white/10 px-3 py-2 text-sm text-white"
             href="{{ route('modules.company-settings.tax-rates.index') }}">Tax Rates</a>
          <a class="rounded-lg bg-white/5 hover:bg-white/10 px-3 py-2 text-sm text-white"
             href="{{ route('modules.company-settings.payroll-statutory.index') }}">Payroll Statutory</a>
          <a class="rounded-lg bg-white/5 hover:bg-white/10 px-3 py-2 text-sm text-white"
             href="{{ route('modules.company-settings.paye-brackets.index') }}">PAYE Brackets</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
