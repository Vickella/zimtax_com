@extends('layouts.app')
@section('page_title','Employees')

@section('content')
<div class="h-full flex flex-col gap-4">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-semibold">Employees</h1>
            <p class="text-xs text-slate-300">Maintain employee records and salary structure.</p>
        </div>

        <a href="{{ route('modules.payroll.employees.create') }}"
           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
            ➕ New Employee
        </a>
    </div>

    <div class="rounded-2xl ring-1 ring-white/10 bg-black/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-xs text-slate-300 border-b border-white/10">
                    <tr>
                        <th class="text-left px-3 py-2">Employee No</th>
                        <th class="text-left px-3 py-2">Name</th>
                        <th class="text-left px-3 py-2">Status</th>
                        <th class="text-right px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach($employees as $e)
                        <tr>
                            <td class="px-3 py-2">{{ $e->employee_no }}</td>
                            <td class="px-3 py-2">{{ $e->last_name }}, {{ $e->first_name }}</td>
                            <td class="px-3 py-2">
                                <span class="text-xs px-2 py-1 rounded-lg ring-1 ring-white/10 bg-white/5">
                                    {{ $e->status }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('modules.payroll.employees.edit',$e) }}"
                                   class="text-xs rounded-lg px-3 py-2 bg-white/10 hover:bg-white/15 ring-1 ring-white/10">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-3 border-t border-white/10">
            {{ $employees->links() }}
        </div>
    </div>
</div>
@endsection
