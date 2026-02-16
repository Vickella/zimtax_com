{{-- resources/views/modules/purchases/suppliers/edit.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Edit Supplier</h1>
            <p class="text-sm text-gray-500">{{ $supplier->code }} — {{ $supplier->name }}</p>
        </div>
        <a href="{{ route('modules.purchases.suppliers.index') }}" class="px-4 py-2 border rounded">Back</a>
    </div>

    <form method="POST" action="{{ route('modules.purchases.suppliers.update', $supplier) }}" class="border rounded p-4">
        @csrf
        @method('PUT')
        @include('modules.purchases.suppliers._form', ['supplier' => $supplier])
        <div class="mt-6 flex gap-2">
            <button class="px-4 py-2 border rounded bg-black text-white" type="submit">Update</button>
            <a class="px-4 py-2 border rounded" href="{{ route('modules.purchases.suppliers.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
