@extends('layouts.erp')
@section('page_title','Edit Sales Invoice')

@section('content')
<div class="h-full overflow-auto">
    <form method="POST" action="{{ route('sales.invoices.update',$invoice) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-4">
            @include('modules.sales.invoices._form')
        </div>

        <button class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm">Update Draft</button>
    </form>
</div>
@endsection
