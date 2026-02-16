{{-- resources/views/modules/purchases/invoices/show.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Purchase Invoice {{ $invoice->invoice_no }}</h1>
            <p class="text-sm text-gray-500">{{ $invoice->supplier->name ?? '-' }} • {{ $invoice->status }}</p>
        </div>
        <div class="flex gap-2">
            @if($invoice->status === 'DRAFT')
                <a href="{{ route('modules.purchases.invoices.edit', $invoice) }}" class="px-4 py-2 border rounded">Edit</a>
                <form method="POST" action="{{ route('modules.purchases.invoices.submit', $invoice) }}">
                    @csrf
                    <button class="px-4 py-2 border rounded bg-black text-white" type="submit">Submit</button>
                </form>
            @elseif($invoice->status === 'SUBMITTED')
                <form method="POST" action="{{ route('modules.purchases.invoices.cancel', $invoice) }}">
                    @csrf
                    <button class="px-4 py-2 border rounded" type="submit">Cancel</button>
                </form>
            @endif
            <a href="{{ route('modules.purchases.invoices.index') }}" class="px-4 py-2 border rounded">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 border rounded bg-green-50 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="border rounded p-4">
            <div class="text-sm text-gray-500 mb-2">Header</div>
            <div class="text-sm"><span class="font-medium">Posting Date:</span> {{ $invoice->posting_date?->format('Y-m-d') }}</div>
            <div class="text-sm"><span class="font-medium">Due Date:</span> {{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</div>
            <div class="text-sm"><span class="font-medium">Currency:</span> {{ $invoice->currency }}</div>
            <div class="text-sm"><span class="font-medium">Exchange Rate:</span> {{ $invoice->exchange_rate }}</div>
            <div class="text-sm"><span class="font-medium">Supplier Inv No:</span> {{ $invoice->supplier_invoice_no ?? '-' }}</div>
            <div class="text-sm"><span class="font-medium">Input Tax Doc:</span> {{ $invoice->input_tax_document_ref ?? '-' }}</div>
            <div class="text-sm"><span class="font-medium">Bill of Entry:</span> {{ $invoice->bill_of_entry_ref ?? '-' }}</div>
            <div class="text-sm"><span class="font-medium">Remarks:</span> {{ $invoice->remarks ?? '-' }}</div>
        </div>

        <div class="border rounded p-4">
            <div class="text-sm text-gray-500 mb-2">Totals</div>
            <div class="text-sm"><span class="font-medium">Subtotal:</span> {{ number_format((float)$invoice->subtotal, 2) }} {{ $invoice->currency }}</div>
            <div class="text-sm"><span class="font-medium">VAT:</span> {{ number_format((float)$invoice->vat_amount, 2) }} {{ $invoice->currency }}</div>
            <div class="text-sm text-lg mt-2"><span class="font-semibold">Total:</span> {{ number_format((float)$invoice->total, 2) }} {{ $invoice->currency }}</div>
        </div>
    </div>

    <div class="mt-6 border rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-3">Item</th>
                    <th class="text-left p-3">Warehouse</th>
                    <th class="text-right p-3">Qty</th>
                    <th class="text-right p-3">Rate</th>
                    <th class="text-right p-3">Amount</th>
                    <th class="text-right p-3">VAT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lines as $ln)
                    <tr class="border-t">
                        <td class="p-3">
                            <div class="font-medium">{{ $ln->item->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $ln->item->sku ?? '' }}</div>
                            @if($ln->description)
                                <div class="text-xs text-gray-600 mt-1">{{ $ln->description }}</div>
                            @endif
                        </td>
                        <td class="p-3">{{ $ln->warehouse->name ?? '-' }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$ln->qty, 4) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$ln->rate, 6) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$ln->amount, 2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$ln->vat_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
