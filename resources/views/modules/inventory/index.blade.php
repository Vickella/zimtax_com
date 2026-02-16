{{-- resources/views/modules/inventory/index.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Inventory</h1>
            <p class="text-sm text-gray-500">Manage items, warehouses and stock movements.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('modules.inventory.items.create') }}" class="px-4 py-2 border rounded">New Item</a>
            <a href="{{ route('modules.inventory.warehouses.create') }}" class="px-4 py-2 border rounded">New Warehouse</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 border rounded">
            <div class="text-sm text-gray-500">Active Items</div>
            <div class="text-2xl font-bold">{{ $stats['items'] ?? 0 }}</div>
        </div>
        <div class="p-4 border rounded">
            <div class="text-sm text-gray-500">Active Warehouses</div>
            <div class="text-2xl font-bold">{{ $stats['warehouses'] ?? 0 }}</div>
        </div>
        <div class="p-4 border rounded">
            <div class="text-sm text-gray-500">Ledger Entries Today</div>
            <div class="text-2xl font-bold">{{ $stats['ledger_entries_today'] ?? 0 }}</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <a class="p-4 border rounded hover:bg-gray-50" href="{{ route('modules.inventory.items.index') }}">
            <div class="text-lg font-medium">Items</div>
            <div class="text-sm text-gray-500">Create and maintain stock and service items.</div>
        </a>

        <a class="p-4 border rounded hover:bg-gray-50" href="{{ route('modules.inventory.warehouses.index') }}">
            <div class="text-lg font-medium">Warehouses</div>
            <div class="text-sm text-gray-500">Maintain storage locations for stock movements.</div>
        </a>

        <a class="p-4 border rounded hover:bg-gray-50" href="{{ route('modules.inventory.stock-ledger.index') }}">
            <div class="text-lg font-medium">Stock Ledger</div>
            <div class="text-sm text-gray-500">View all inventory in/out entries (append-only).</div>
        </a>
    </div>
</div>
@endsection
