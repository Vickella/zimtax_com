{{-- resources/views/modules/inventory/stock_ledger/index.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Stock Ledger</h1>
            <p class="text-sm text-gray-500">Append-only inventory movements (auditable).</p>
        </div>
        <a href="{{ route('modules.inventory.index') }}" class="px-4 py-2 border rounded">Back</a>
    </div>

    <div class="border rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-3">Date</th>
                    <th class="text-left p-3">Time</th>
                    <th class="text-left p-3">Item</th>
                    <th class="text-left p-3">Warehouse</th>
                    <th class="text-left p-3">Voucher</th>
                    <th class="text-right p-3">Qty</th>
                    <th class="text-right p-3">Unit Cost</th>
                    <th class="text-right p-3">Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $e)
                    <tr class="border-t">
                        <td class="p-3">{{ optional($e->posting_date)->format('Y-m-d') ?? $e->posting_date }}</td>
                        <td class="p-3">{{ $e->posting_time }}</td>
                        <td class="p-3">
                            <div class="font-medium">{{ $e->item->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $e->item->sku ?? '' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium">{{ $e->warehouse->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $e->warehouse->code ?? '' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium">{{ $e->voucher_type }}</div>
                            <div class="text-xs text-gray-500">#{{ $e->voucher_id }}</div>
                        </td>
                        <td class="p-3 text-right {{ (float)$e->qty < 0 ? 'text-red-700' : 'text-green-700' }}">
                            {{ number_format((float)$e->qty, 4) }}
                        </td>
                        <td class="p-3 text-right">
                            {{ $e->unit_cost === null ? '-' : number_format((float)$e->unit_cost, 6) }}
                        </td>
                        <td class="p-3 text-right">
                            {{ $e->value_change === null ? '-' : number_format((float)$e->value_change, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-6 text-center text-gray-500" colspan="8">No stock ledger entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>
</div>
@endsection
