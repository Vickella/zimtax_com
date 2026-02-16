@extends('layouts.erp')
@section('page_title','Customers')

@section('content')
<div class="h-full overflow-auto space-y-4">
    @if(session('ok'))
        <div class="p-3 rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20 text-emerald-200 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex items-center justify-between gap-3">
        <div class="text-sm text-slate-300">Manage customer master data.</div>
        <a href="{{ route('modules.sales.customers.create') }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">+ New Customer</a>
    </div>

    <div class="rounded-xl ring-1 ring-white/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-white/5 text-slate-200">
                <tr>
                    <th class="p-3 text-left">Code</th>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">TIN</th>
                    <th class="p-3 text-left">VAT</th>
                    <th class="p-3 text-left">Active</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach($customers as $c)
                <tr class="hover:bg-white/5">
                    <td class="p-3">{{ $c->code }}</td>
                    <td class="p-3">{{ $c->name }}</td>
                    <td class="p-3">{{ $c->tin }}</td>
                    <td class="p-3">{{ $c->vat_number }}</td>
                    <td class="p-3">{{ $c->is_active ? 'Yes' : 'No' }}</td>
                    <td class="p-3 text-right">
                        <a class="text-indigo-200 hover:underline" href="{{ route('modules.sales.customers.edit',$c) }}">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>{{ $customers->links() }}</div>
</div>
@endsection
