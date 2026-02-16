{{-- resources/views/modules/purchases/invoices/edit.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Edit Purchase Invoice</h1>
            <p class="text-sm text-gray-500">{{ $invoice->invoice_no }} — {{ $invoice->status }}</p>
        </div>
        <a href="{{ route('modules.purchases.invoices.show', $invoice) }}" class="px-4 py-2 border rounded">Back</a>
    </div>

    <form method="POST" action="{{ route('modules.purchases.invoices.update', $invoice) }}" class="border rounded p-4">
        @csrf
        @method('PUT')
        @include('modules.purchases.invoices._form', ['invoice' => $invoice])
        <div class="mt-6 flex gap-2">
            <button class="px-4 py-2 border rounded bg-black text-white" type="submit">Update Draft</button>
            <a class="px-4 py-2 border rounded" href="{{ route('modules.purchases.invoices.show', $invoice) }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
