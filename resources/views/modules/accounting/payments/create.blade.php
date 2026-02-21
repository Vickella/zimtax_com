@extends('layouts.app')

@section('page_title', 'Create Payment')

@section('content')
<div class="max-w-7xl mx-auto">
    <form method="POST" action="{{ route('modules.accounting.payments.store') }}" id="paymentForm">
        @csrf
        @include('modules.accounting.payments._form', [
            'payment' => $payment,
            'accounts' => $accounts,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'openSalesInvoices' => $openSalesInvoices,
            'openPurchaseInvoices' => $openPurchaseInvoices
        ])
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
    const submitButton = e.submitter;
    if (submitButton && submitButton.value === 'submit') {
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        `;
    }
});
</script>
@endpush