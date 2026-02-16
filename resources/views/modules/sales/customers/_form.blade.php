@php($editing = isset($customer))

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="text-xs text-slate-300">Code</label>
        <input name="code" value="{{ old('code', $customer->code ?? '') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" required>
    </div>
    <div>
        <label class="text-xs text-slate-300">Name</label>
        <input name="name" value="{{ old('name', $customer->name ?? '') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" required>
    </div>

    <div>
        <label class="text-xs text-slate-300">TIN</label>
        <input name="tin" value="{{ old('tin', $customer->tin ?? '') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10">
    </div>
    <div>
        <label class="text-xs text-slate-300">VAT Number</label>
        <input name="vat_number" value="{{ old('vat_number', $customer->vat_number ?? '') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10">
    </div>

    <div class="md:col-span-2">
        <label class="text-xs text-slate-300">Address</label>
        <textarea name="address" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" rows="3">{{ old('address', $customer->address ?? '') }}</textarea>
    </div>

    <div>
        <label class="text-xs text-slate-300">Phone</label>
        <input name="phone" value="{{ old('phone', $customer->phone ?? '') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10">
    </div>
    <div>
        <label class="text-xs text-slate-300">Email</label>
        <input name="email" value="{{ old('email', $customer->email ?? '') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10">
    </div>

    <div>
        <label class="text-xs text-slate-300">Credit Limit</label>
        <input name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit ?? '') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10">
    </div>

    <div>
        <label class="text-xs text-slate-300">Currency (optional)</label>
        <input name="currency" value="{{ old('currency', $customer->currency ?? '') }}" class="w-full mt-1 px-3 py-2 rounded-lg bg-black/20 ring-1 ring-white/10" placeholder="USD">
    </div>

    <div class="flex items-center gap-2 md:col-span-2">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}>
        <span class="text-sm text-slate-200">Active</span>
    </div>
</div>
    