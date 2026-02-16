{{-- resources/views/modules/inventory/warehouses/edit.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Edit Warehouse</h1>
            <p class="text-sm text-gray-500">{{ $warehouse->code }} — {{ $warehouse->name }}</p>
        </div>
        <a href="{{ route('modules.inventory.warehouses.index') }}" class="px-4 py-2 border rounded">Back</a>
    </div>

    <form method="POST" action="{{ route('modules.inventory.warehouses.update', $warehouse) }}" class="border rounded p-4">
        @csrf
        @method('PUT')
        @include('modules.inventory.warehouses._form', ['warehouse' => $warehouse])
        <div class="mt-6 flex gap-2">
            <button class="px-4 py-2 border rounded bg-black text-white" type="submit">Update</button>
            <a class="px-4 py-2 border rounded" href="{{ route('modules.inventory.warehouses.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
