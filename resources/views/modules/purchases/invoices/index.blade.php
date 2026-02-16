{{-- resources/views/modules/purchases/invoices/index.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Purchase Invoices</h1>
            <p class="text-sm text-gray-500">Draft and submitted supplier invoices.</p>
        </div>
        <a href="{{ route('modules.purchases.invoices.create') }}" class="px-4 py-2 border rounded">New Purchase Invoice</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 border rounded bg-green-50 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="border rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-3">Invoice No</th>
                    <th class="text-left p-3">Supplier</th>
                    <th class="text-left p-3">Posting Date</th>
                    <th class="text-left p-3">Due Date</th>
                    <th class="text-left p-3">Status</th>
                    <th class="text-right p-3">Total</th>
                    <th class="text-right p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr class="border-t">
                        <td class="p-3">
                            <a class="underline" href="{{ route('modules.purchases.invoices.show', $inv) }}">{{ $inv->invoice_no }}</a>
                        </td>
                        <td class="p-3">{{ $inv->supplier->name ?? '-' }}</td>
                        <td class="p-3">{{ $inv->posting_date?->format('Y-m-d') }}</td>
                        <td class="p-3">{{ $inv->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="p-3">{{ $inv->status }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$inv->total, 2) }} {{ $inv->currency }}</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('modules.purchases.invoices.show', $inv) }}" class="px-3 py-1 border rounded">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-6 text-center text-gray-500" colspan="7">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
@endsection
