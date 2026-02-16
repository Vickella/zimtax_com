{{-- resources/views/modules/inventory/items/index.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Items</h1>
            <p class="text-sm text-gray-500">Company items (stock and services).</p>
        </div>
        <a href="{{ route('modules.inventory.items.create') }}" class="px-4 py-2 border rounded">New Item</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 border rounded bg-green-50 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="border rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-3">SKU</th>
                    <th class="text-left p-3">Name</th>
                    <th class="text-left p-3">Type</th>
                    <th class="text-left p-3">UOM</th>
                    <th class="text-right p-3">Cost</th>
                    <th class="text-right p-3">Selling</th>
                    <th class="text-left p-3">VAT</th>
                    <th class="text-left p-3">Active</th>
                    <th class="text-right p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="border-t">
                        <td class="p-3">{{ $item->sku }}</td>
                        <td class="p-3">{{ $item->name }}</td>
                        <td class="p-3">{{ $item->item_type }}</td>
                        <td class="p-3">{{ $item->uom }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$item->cost_price, 2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$item->selling_price, 2) }}</td>
                        <td class="p-3">{{ $item->vat_category ?? '-' }}</td>
                        <td class="p-3">
                            @if($item->is_active)
                                <span class="px-2 py-1 text-xs border rounded bg-green-50 text-green-800">Yes</span>
                            @else
                                <span class="px-2 py-1 text-xs border rounded bg-gray-50 text-gray-700">No</span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            <a href="{{ route('modules.inventory.items.edit', $item) }}" class="px-3 py-1 border rounded">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-6 text-center text-gray-500" colspan="9">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
@endsection
