@extends('layouts.app')

@section('page_title','Chart of Accounts')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div class="text-sm text-slate-300">Company COA</div>
    <a href="{{ route('modules.accounting.chart.create') }}"
       class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">
        + New Account
    </a>
</div>

<div class="rounded-xl ring-1 ring-white/10 overflow-hidden bg-black/10">
    <table class="w-full text-sm">
        <thead class="bg-white/5">
        <tr>
            <th class="p-3 text-left">Code</th>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Type</th>
            <th class="p-3 text-left">Control</th>
            <th class="p-3 text-left">Active</th>
            <th class="p-3"></th>
        </tr>
        </thead>
        <tbody class="divide-y divide-white/10">
        @foreach($accounts as $a)
            <tr>
                <td class="p-3 font-mono">{{ $a->code }}</td>
                <td class="p-3">{{ $a->name }}</td>
                <td class="p-3">{{ $a->account_type }}</td>
                <td class="p-3">{{ $a->is_control_account ? 'Yes' : 'No' }}</td>
                <td class="p-3">{{ $a->is_active ? 'Yes' : 'No' }}</td>
                <td class="p-3 text-right">
                    <a class="text-indigo-300 hover:underline"
                       href="{{ route('modules.accounting.chart.edit',$a) }}">Edit</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
