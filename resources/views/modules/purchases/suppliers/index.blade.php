{{-- resources/views/modules/purchases/suppliers/index.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Suppliers</h1>
            <p class="text-sm text-gray-500">Supplier master data.</p>
        </div>
        <a href="{{ route('modules.purchases.suppliers.create') }}" class="px-4 py-2 border rounded">New Supplier</a>
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
                    <th class="text-left p-3">TIN</th>
                    <th class="text-left p-3">VAT</th>
                    <th class="text-left p-3">WHT</th>
                    <th class="text-left p-3">Active</th>
                    <th class="text-right p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                    <tr class="border-t">
                        <td class="p-3">{{ $s->code }}</td>
                        <td class="p-3">{{ $s->name }}</td>
                        <td class="p-3">{{ $s->tin ?? '-' }}</td>
                        <td class="p-3">{{ $s->vat_number ?? '-' }}</td>
                        <td class="p-3">
                            @if($s->withholding_tax_flag)
                                <span class="px-2 py-1 text-xs border rounded bg-yellow-50 text-yellow-800">Yes</span>
                            @else
                                <span class="px-2 py-1 text-xs border rounded bg-gray-50 text-gray-700">No</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($s->is_active)
                                <span class="px-2 py-1 text-xs border rounded bg-green-50 text-green-800">Yes</span>
                            @else
                                <span class="px-2 py-1 text-xs border rounded bg-gray-50 text-gray-700">No</span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            <a href="{{ route('modules.purchases.suppliers.edit', $s) }}" class="px-3 py-1 border rounded">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-6 text-center text-gray-500" colspan="7">No suppliers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $suppliers->links() }}</div>
</div>
@endsection
