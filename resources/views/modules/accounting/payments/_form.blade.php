@php
    $payment ??= null;
    $editing = filled($payment?->id);
    
    $oldAlloc = old('allocations');
    $allocations = $oldAlloc ? collect($oldAlloc) : collect($payment?->allocations ?? []);
@endphp

<div class="space-y-6">
    {{-- Main payment details card --}}
    <div class="rounded-xl ring-1 ring-white/10 bg-black/20 p-5">
        <h3 class="text-sm font-semibold text-white mb-4">Payment Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Payment Type --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1">Payment Type</label>
                <select name="payment_type" id="payment_type" required
                        class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    <option value="RECEIVE" {{ old('payment_type', $payment?->payment_type) === 'RECEIVE' ? 'selected' : '' }}>💰 Receive (Customer Payment)</option>
                    <option value="PAY" {{ old('payment_type', $payment?->payment_type) === 'PAY' ? 'selected' : '' }}>💸 Pay (Supplier Payment)</option>
                </select>
            </div>
            
            {{-- Posting Date --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1">Posting Date</label>
                <input type="date" name="posting_date" 
                       value="{{ old('posting_date', optional($payment?->posting_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                       required>
            </div>
            
            {{-- Payment Account --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1">Payment Account</label>
                <select name="payment_account_id" id="payment_account_id" required
                        class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    <option value="">Select account...</option>
                    @foreach($accounts ?? [] as $acc)
                        <option value="{{ $acc->id }}" {{ old('payment_account_id', $payment?->payment_account_id) == $acc->id ? 'selected' : '' }}>
                            {{ $acc->code }} - {{ $acc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- Currency --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1">Currency</label>
                <input type="text" name="currency" id="currency" 
                       value="{{ old('currency', $payment?->currency ?? 'USD') }}"
                       class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                       required>
            </div>
            
            {{-- Exchange Rate --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1">Exchange Rate</label>
                <input type="number" name="exchange_rate" id="exchange_rate" step="0.0001" min="0"
                       value="{{ old('exchange_rate', $payment?->exchange_rate ?? 1) }}"
                       class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                       required>
            </div>
            
            {{-- Amount --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1">Amount</label>
                <input type="number" name="amount" id="amount" step="0.01" min="0"
                       value="{{ old('amount', $payment?->amount ?? 0) }}"
                       class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                       required>
                <p class="text-xs text-slate-500 mt-1">Total allocated cannot exceed amount</p>
            </div>
            
            {{-- Reference No --}}
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Reference Number</label>
                <input type="text" name="reference_no" 
                       value="{{ old('reference_no', $payment?->reference_no ?? '') }}"
                       placeholder="Check #, Transfer Ref, Receipt #"
                       class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
            </div>
            
            {{-- Reference Date --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1">Reference Date</label>
                <input type="date" name="reference_date"
                       value="{{ old('reference_date', optional($payment?->reference_date)->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
            </div>
            
            {{-- Remarks --}}
            <div class="md:col-span-3">
                <label class="block text-xs text-slate-400 mb-1">Remarks / Notes</label>
                <input type="text" name="remarks"
                       value="{{ old('remarks', $payment?->remarks ?? '') }}"
                       placeholder="Additional notes about this payment..."
                       class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
            </div>
        </div>
    </div>
    
    {{-- Party selection card --}}
    <div class="rounded-xl ring-1 ring-white/10 bg-black/20 p-5">
        <h3 class="text-sm font-semibold text-white mb-4">Party Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Customer selection --}}
            <div id="customer_section" style="{{ old('payment_type', $payment?->payment_type) === 'PAY' ? 'display: none;' : 'display: block;' }}">
                <label class="block text-xs text-slate-400 mb-1">Customer</label>
                <select name="customer_id" id="customer_id"
                        class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    <option value="">Select customer...</option>
                    @foreach($customers ?? [] as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id', $payment?->customer_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- Supplier selection --}}
            <div id="supplier_section" style="{{ old('payment_type', $payment?->payment_type) === 'RECEIVE' ? 'display: none;' : 'display: block;' }}">
                <label class="block text-xs text-slate-400 mb-1">Supplier</label>
                <select name="supplier_id" id="supplier_id"
                        class="w-full px-3 py-2 rounded-lg bg-black/30 text-white border border-white/10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    <option value="">Select supplier...</option>
                    @foreach($suppliers ?? [] as $s)
                        <option value="{{ $s->id }}" {{ old('supplier_id', $payment?->supplier_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    
    {{-- Allocations card --}}
    <div class="rounded-xl ring-1 ring-white/10 bg-black/20 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-white">Invoice Allocations</h3>
                <p class="text-xs text-slate-400">Apply this payment to specific invoices</p>
            </div>
            <button type="button" id="addAllocationBtn" 
                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Allocation
            </button>
        </div>
        
        {{-- Summary cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="bg-black/40 rounded-lg p-3 ring-1 ring-white/5">
                <div class="text-xs text-slate-400">Payment Amount</div>
                <div class="text-lg font-semibold text-white" id="summaryAmount">{{ number_format(old('amount', $payment?->amount ?? 0), 2) }}</div>
            </div>
            <div class="bg-black/40 rounded-lg p-3 ring-1 ring-white/5">
                <div class="text-xs text-slate-400">Total Allocated</div>
                <div class="text-lg font-semibold text-emerald-400" id="summaryAllocated">0.00</div>
            </div>
            <div class="bg-black/40 rounded-lg p-3 ring-1 ring-white/5">
                <div class="text-xs text-slate-400">Unallocated</div>
                <div class="text-lg font-semibold text-amber-400" id="summaryUnallocated">{{ number_format(old('amount', $payment?->amount ?? 0), 2) }}</div>
            </div>
        </div>
        
        {{-- Allocations table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="allocationsTable">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left py-2 px-1 text-xs font-medium text-slate-400">Invoice</th>
                        <th class="text-right py-2 px-1 text-xs font-medium text-slate-400">Outstanding</th>
                        <th class="text-right py-2 px-1 text-xs font-medium text-slate-400">Allocated</th>
                        <th class="text-right py-2 px-1 text-xs font-medium text-slate-400 w-16">Action</th>
                    </tr>
                </thead>
                <tbody id="allocationsBody">
                    @php $rowIndex = 0; @endphp
                    @forelse($allocations as $alloc)
                        @php
                            $invType = is_array($alloc) ? ($alloc['invoice_type'] ?? '') : ($alloc->invoice_type ?? '');
                            $invId = is_array($alloc) ? ($alloc['invoice_id'] ?? '') : ($alloc->invoice_id ?? '');
                            $allocAmount = is_array($alloc) ? ($alloc['allocated_amount'] ?? 0) : ($alloc->allocated_amount ?? 0);
                        @endphp
                        <tr class="allocation-row border-b border-white/5">
                            <td class="py-2 px-1">
                                <select name="allocations[{{ $rowIndex }}][invoice_key]" 
                                        class="invoice-select w-full px-2 py-1.5 rounded bg-black/40 text-white border border-white/10 focus:border-indigo-500 outline-none text-sm">
                                    <option value="">Select invoice...</option>
                                    @foreach($openSalesInvoices ?? [] as $inv)
                                        <option value="SI:{{ $inv->id }}" 
                                                data-outstanding="{{ $inv->outstanding }}"
                                                data-type="SalesInvoice"
                                                data-id="{{ $inv->id }}"
                                                {{ $invType === 'SalesInvoice' && $invId == $inv->id ? 'selected' : '' }}>
                                            {{ $inv->invoice_no }} (Sales) - Due: {{ number_format($inv->outstanding, 2) }}
                                        </option>
                                    @endforeach
                                    @foreach($openPurchaseInvoices ?? [] as $inv)
                                        <option value="PI:{{ $inv->id }}"
                                                data-outstanding="{{ $inv->outstanding }}"
                                                data-type="PurchaseInvoice"
                                                data-id="{{ $inv->id }}"
                                                {{ $invType === 'PurchaseInvoice' && $invId == $inv->id ? 'selected' : '' }}>
                                            {{ $inv->invoice_no }} (Purchase) - Due: {{ number_format($inv->outstanding, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="allocations[{{ $rowIndex }}][invoice_type]" value="{{ $invType }}" class="invoice-type">
                                <input type="hidden" name="allocations[{{ $rowIndex }}][invoice_id]" value="{{ $invId }}" class="invoice-id">
                            </td>
                            <td class="py-2 px-1 text-right">
                                <span class="outstanding-amount text-slate-300">{{ number_format($allocAmount, 2) }}</span>
                            </td>
                            <td class="py-2 px-1 text-right">
                                <input type="number" name="allocations[{{ $rowIndex }}][allocated_amount]" 
                                       value="{{ $allocAmount }}"
                                       step="0.01" min="0"
                                       class="alloc-amount w-28 px-2 py-1.5 rounded bg-black/40 text-white border border-white/10 focus:border-indigo-500 outline-none text-right text-sm">
                            </td>
                            <td class="py-2 px-1 text-right">
                                <button type="button" class="remove-row text-rose-400 hover:text-rose-300 px-2 py-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @php $rowIndex++; @endphp
                    @empty
                        <tr class="no-allocations-row">
                            <td colspan="4" class="py-4 text-center text-slate-400">No allocations added yet. Click "Add Allocation" to start.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Template for new rows --}}
<template id="allocationRowTemplate">
    <tr class="allocation-row border-b border-white/5">
        <td class="py-2 px-1">
            <select class="invoice-select w-full px-2 py-1.5 rounded bg-black/40 text-white border border-white/10 focus:border-indigo-500 outline-none text-sm">
                <option value="">Select invoice...</option>
            </select>
            <input type="hidden" class="invoice-type" name="" value="">
            <input type="hidden" class="invoice-id" name="" value="">
        </td>
        <td class="py-2 px-1 text-right">
            <span class="outstanding-amount text-slate-300">0.00</span>
        </td>
        <td class="py-2 px-1 text-right">
            <input type="number" step="0.01" min="0" value="0"
                   class="alloc-amount w-28 px-2 py-1.5 rounded bg-black/40 text-white border border-white/10 focus:border-indigo-500 outline-none text-right text-sm">
        </td>
        <td class="py-2 px-1 text-right">
            <button type="button" class="remove-row text-rose-400 hover:text-rose-300 px-2 py-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </td>
    </tr>
</template>

{{-- Form Actions --}}
<div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-white/10">
    <a href="{{ route('modules.accounting.payments.index') }}" 
       class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 ring-1 ring-white/10 text-sm transition-colors">
        Cancel
    </a>
    
    <button type="submit" name="action" value="draft" 
            class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 ring-1 ring-white/10 text-sm transition-colors">
        💾 Save Draft
    </button>
    
    <button type="submit" name="action" value="submit" id="submitBtn"
            class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors flex items-center gap-2">
        <span>Submit & Post to GL</span>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple toggle for customer/supplier
    const paymentType = document.getElementById('payment_type');
    const customerSection = document.getElementById('customer_section');
    const supplierSection = document.getElementById('supplier_section');
    
    if (paymentType && customerSection && supplierSection) {
        function toggleSections() {
            if (paymentType.value === 'RECEIVE') {
                customerSection.style.display = 'block';
                supplierSection.style.display = 'none';
            } else {
                customerSection.style.display = 'none';
                supplierSection.style.display = 'block';
            }
        }
        
        toggleSections();
        paymentType.addEventListener('change', toggleSections);
    }
    
    // Add allocation button
    const addBtn = document.getElementById('addAllocationBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const tbody = document.getElementById('allocationsBody');
            const template = document.getElementById('allocationRowTemplate');
            const clone = template.content.cloneNode(true);
            
            // Remove no-allocations row if exists
            const noAllocRow = tbody.querySelector('.no-allocations-row');
            if (noAllocRow) {
                noAllocRow.remove();
            }
            
            tbody.appendChild(clone);
            updateSummaries();
        });
    }
    
    // Remove row buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const row = e.target.closest('tr');
            const tbody = document.getElementById('allocationsBody');
            if (tbody.children.length > 1) {
                row.remove();
                updateSummaries();
            }
        }
    });
    
    // Update summaries
    function updateSummaries() {
        const amount = parseFloat(document.getElementById('amount')?.value || 0);
        let totalAllocated = 0;
        
        document.querySelectorAll('.alloc-amount').forEach(input => {
            totalAllocated += parseFloat(input.value || 0);
        });
        
        document.getElementById('summaryAmount').textContent = amount.toFixed(2);
        document.getElementById('summaryAllocated').textContent = totalAllocated.toFixed(2);
        document.getElementById('summaryUnallocated').textContent = (amount - totalAllocated).toFixed(2);
    }
    
    // Listen for changes
    document.getElementById('amount')?.addEventListener('input', updateSummaries);
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('alloc-amount')) {
            updateSummaries();
        }
    });
});
</script>