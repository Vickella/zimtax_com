{{-- resources/views/modules/inventory/warehouses/index.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Warehouses</h1>
            <p class="text-sm text-gray-500">Storage locations for stock movements.</p>
        </div>
        <a href="{{ route('modules.inventory.warehouses.create') }}" class="px-4 py-2 border rounded">New Warehouse</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 border rounded bg-green-50 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="border rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-3">Code</th>
                    <th class="text-left p-3">Name</th>
                    <th class="text-left p-3">Location</th>
                    <th class="text-left p-3">Active</th>
                    <th class="text-right p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                    <tr class="border-t">
                        <td class="p-3">{{ $warehouse->code }}</td>
                        <td class="p-3">{{ $warehouse->name }}</td>
                        <td class="p-3">{{ $warehouse->location ?? '-' }}</td>
                        <td class="p-3">
                            @if($warehouse->is_active)
                                <span class="px-2 py-1 text-xs border rounded bg-green-50 text-green-800">Yes</span>
                            @else
                                <span class="px-2 py-1 text-xs border rounded bg-gray-50 text-gray-700">No</span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            <a href="{{ route('modules.inventory.warehouses.edit', $warehouse) }}" class="px-3 py-1 border rounded">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-6 text-center text-gray-500" colspan="5">No warehouses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $warehouses->links() }}
    </div>
</div>
@endsection
