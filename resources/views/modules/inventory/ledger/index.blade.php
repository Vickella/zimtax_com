@extends('layouts.app')

@section('page_title', 'Stock Ledger')

@section('content')
<div class="space-y-4">

    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-lg font-semibold">Stock Ledger</div>
            <div class="text-sm text-slate-300">
                Append-only stock movements (in/out) by item and warehouse.
            </div>
            <a href="{{ route('modules.index', ['module' => 'inventory']) }}"
               class="inline-flex items-center gap-2 text-sm text-slate-200 hover:text-white mt-2">
                ← Back to Inventory
            </a>
        </div>
    </div>

    {{-- Filters Row --}}
    <form method="GET" action="{{ route('modules.inventory.stock-ledger.index') }}"
          class="rounded-xl ring-1 ring-white/10 bg-black/10 backdrop-blur p-4">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            {{-- Item --}}
            <div class="md:col-span-3">
                <label class="text-xs text-slate-300">Item</label>
                <select name="item_id"
                        class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                    <option value="">All items</option>
                    @foreach($items as $it)
                        <option value="{{ $it->id }}" @selected((string)request('item_id') === (string)$it->id)>
                            {{ $it->name }} ({{ $it->sku }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Warehouse --}}
            <div class="md:col-span-3">
                <label class="text-xs text-slate-300">Warehouse</label>
                <select name="warehouse_id"
                        class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                    <option value="">All warehouses</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string)request('warehouse_id') === (string)$w->id)>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- From --}}
            <div class="md:col-span-2">
                <label class="text-xs text-slate-300">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
            </div>

            {{-- To --}}
            <div class="md:col-span-2">
                <label class="text-xs text-slate-300">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
            </div>

            {{-- Voucher Type --}}
            <div class="md:col-span-2">
                <label class="text-xs text-slate-300">Voucher Type</label>
                <select name="voucher_type"
                        class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                    <option value="All">All</option>
                    @foreach($voucherTypes as $vt)
                        <option value="{{ $vt }}" @selected(request('voucher_type', 'All') === $vt)>
                            {{ $vt }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="md:col-span-12 flex gap-2 pt-1">
                <button type="submit"
                        class="rounded-lg px-4 py-2 text-sm bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                    Apply
                </button>

                <a href="{{ route('modules.inventory.stock-ledger.index') }}"
                   class="rounded-lg px-4 py-2 text-sm bg-black/20 hover:bg-black/30 ring-1 ring-white/10">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white/5">
                <tr>
                    <th class="p-3 text-left whitespace-nowrap">Date</th>
                    <th class="p-3 text-left whitespace-nowrap">Time</th>
                    <th class="p-3 text-left whitespace-nowrap">Item</th>
                    <th class="p-3 text-left whitespace-nowrap">Warehouse</th>
                    <th class="p-3 text-right whitespace-nowrap">Qty</th>
                    <th class="p-3 text-right whitespace-nowrap">Unit Cost</th>
                    <th class="p-3 text-right whitespace-nowrap">Value Change</th>
                    <th class="p-3 text-right whitespace-nowrap">Stock Balance</th>
                    <th class="p-3 text-left whitespace-nowrap">Voucher</th>
                    <th class="p-3 text-right whitespace-nowrap">Voucher ID</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                @forelse($entries as $e)
                    <tr class="hover:bg-white/5">
                        <td class="p-3 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($e->posting_date)->format('Y-m-d') }}</td>
                        <td class="p-3 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($e->posting_time)->format('H:i:s') }}</td>
                        <td class="p-3">
                            <div class="font-medium text-slate-100">{{ $e->item?->name ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $e->item?->sku ?? '' }}</div>
                        </td>
                        <td class="p-3 whitespace-nowrap">{{ $e->warehouse?->name ?? '—' }}</td>

                        <td class="p-3 text-right font-medium whitespace-nowrap {{ $e->qty < 0 ? 'text-red-300' : 'text-emerald-300' }}">
                            {{ number_format((float)$e->qty, 4) }}
                        </td>

                        <td class="p-3 text-right whitespace-nowrap">
                            {{ $e->unit_cost !== null ? number_format((float)$e->unit_cost, 6) : '—' }}
                        </td>

                        <td class="p-3 text-right whitespace-nowrap">
                            {{ $e->value_change !== null ? number_format((float)$e->value_change, 2) : '—' }}
                        </td>

                        <td class="p-3 text-right whitespace-nowrap">
                            {{ $e->stock_balance !== null ? number_format((float)$e->stock_balance, 4) : '—' }}
                        </td>

                        <td class="p-3 whitespace-nowrap">{{ $e->voucher_type }}</td>
                        <td class="p-3 text-right whitespace-nowrap">{{ $e->voucher_id }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="p-6 text-center text-slate-300">
                            No stock ledger entries found for the selected filters.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-t border-white/10">
            {{ $entries->links() }}
        </div>
    </div>
</div>
@endsection
