{{-- resources/views/modules/inventory/items/create.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">New Item</h1>
            <p class="text-sm text-gray-500">Create an item for sales/purchases/inventory.</p>
        </div>
        <a href="{{ route('modules.inventory.items.index') }}" class="px-4 py-2 border rounded">Back</a>
    </div>

    <form method="POST" action="{{ route('modules.inventory.items.store') }}" class="border rounded p-4">
        @csrf
        @include('modules.inventory.items._form')
        <div class="mt-6 flex gap-2">
            <button class="px-4 py-2 border rounded bg-black text-white" type="submit">Save</button>
            <a class="px-4 py-2 border rounded" href="{{ route('modules.inventory.items.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
