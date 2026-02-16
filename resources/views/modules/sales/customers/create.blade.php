@extends('layouts.erp')
@section('page_title','New Customer')

@section('content')
<div class="h-full overflow-auto">
    <form method="POST" action="{{ route('modules.sales.customers.store') }}" class="space-y-4">
        @csrf

        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-4 space-y-4">
            @include('modules.sales.customers._form')
        </div>

        <button class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">Save</button>
    </form>
</div>
@endsection
