{{-- resources/views/modules/purchases/suppliers/_form.blade.php --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Code <span class="text-red-600">*</span></label>
        <input name="code" value="{{ old('code', $supplier->code ?? '') }}" class="w-full border rounded px-3 py-2" required>
        @error('code') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
        <input name="name" value="{{ old('name', $supplier->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
        @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">TIN</label>
        <input name="tin" value="{{ old('tin', $supplier->tin ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">VAT Number</label>
        <input name="vat_number" value="{{ old('vat_number', $supplier->vat_number ?? '') }}" class="w-full border rounded px-3 py-2">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Bank Details</label>
        <textarea name="bank_details" class="w-full border rounded px-3 py-2" rows="3">{{ old('bank_details', $supplier->bank_details ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-2 mt-2">
        @php $wht = old('withholding_tax_flag', $supplier->withholding_tax_flag ?? false); @endphp
        <input id="withholding_tax_flag" name="withholding_tax_flag" type="checkbox" value="1" class="h-4 w-4" @checked((bool)$wht)>
        <label for="withholding_tax_flag" class="text-sm">Withholding Tax Applies</label>
    </div>

    <div class="flex items-center gap-2 mt-2">
        @php $active = old('is_active', $supplier->is_active ?? true); @endphp
        <input id="is_active" name="is_active" type="checkbox" value="1" class="h-4 w-4" @checked((bool)$active)>
        <label for="is_active" class="text-sm">Active</label>
    </div>
</div>
