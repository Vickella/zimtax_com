@extends('layouts.app')

@section('page_title','Edit Payment Entry')

@section('content')
<div class="max-w-6xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold">Edit Payment Entry</h1>
            <p class="text-xs text-slate-300">Update draft payment and allocations.</p>
        </div>
        <a href="{{ route('modules.accounting.payments.show', $payment) }}"
           class="px-3 py-2 rounded-lg bg-white/10 ring-1 ring-white/10 hover:bg-white/15 text-sm">
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('modules.accounting.payments.update', $payment) }}" class="space-y-4">
        @csrf
        @method('PUT')

        @include('modules.accounting.payments._form', [
            'payment' => $payment,
        ])

        <div class="flex items-center justify-end gap-2">
            <button class="px-4 py-2 rounded-lg bg-indigo-500/20 ring-1 ring-indigo-400/30 hover:bg-indigo-500/25 text-sm">
                Update Draft
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/payment-entry.js') }}"></script>
@endpush
