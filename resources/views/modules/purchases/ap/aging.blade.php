{{-- resources/views/modules/purchases/ap/aging.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Accounts Payable Aging</h1>
            <p class="text-sm text-gray-500">Based on submitted purchase invoices.</p>
        </div>
        <a href="{{ route('modules.purchases.index') }}" class="px-4 py-2 border rounded">Back</a>
    </div>

    <div class="border rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-3">Supplier</th>
                    <th class="text-right p-3">Total</th>
                    <th class="text-right p-3">Current</th>
                    <th class="text-right p-3">1–30</th>
                    <th class="text-right p-3">31–60</th>
                    <th class="text-right p-3">61–90</th>
                    <th class="text-right p-3">90+</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr class="border-t">
                        <td class="p-3">{{ $r->supplier->name ?? 'Supplier #'.$r->supplier_id }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r->total_invoiced, 2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r->current_bucket, 2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r->bucket_1_30, 2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r->bucket_31_60, 2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r->bucket_61_90, 2) }}</td>
                        <td class="p-3 text-right">{{ number_format((float)$r->bucket_90_plus, 2) }}</td>
                    </tr>
                @empty
                    <tr><td class="p-6 text-center text-gray-500" colspan="7">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
