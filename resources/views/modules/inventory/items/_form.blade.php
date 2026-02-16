{{-- resources/views/modules/inventory/items/_form.blade.php --}}
@php
    $isEdit = isset($item);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">SKU <span class="text-red-600">*</span></label>
        <input name="sku" value="{{ old('sku', $item->sku ?? '') }}" class="w-full border rounded px-3 py-2" required>
        @error('sku') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
        <input name="name" value="{{ old('name', $item->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
        @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Item Type <span class="text-red-600">*</span></label>
        <select name="item_type" class="w-full border rounded px-3 py-2" required>
            @php $type = old('item_type', $item->item_type ?? 'STOCK'); @endphp
            <option value="STOCK" @selected($type === 'STOCK')>STOCK</option>
            <option value="SERVICE" @selected($type === 'SERVICE')>SERVICE</option>
        </select>
        @error('item_type') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">UOM <span class="text-red-600">*</span></label>
        <input name="uom" value="{{ old('uom', $item->uom ?? 'Units') }}" class="w-full border rounded px-3 py-2" required>
        @error('uom') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Cost Price <span class="text-red-600">*</span></label>
        <input name="cost_price" type="number" step="0.01" min="0" value="{{ old('cost_price', $item->cost_price ?? 0) }}" class="w-full border rounded px-3 py-2" required>
        @error('cost_price') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Selling Price <span class="text-red-600">*</span></label>
        <input name="selling_price" type="number" step="0.01" min="0" value="{{ old('selling_price', $item->selling_price ?? 0) }}" class="w-full border rounded px-3 py-2" required>
        @error('selling_price') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">VAT Category</label>
        <input name="vat_category" value="{{ old('vat_category', $item->vat_category ?? '') }}" class="w-full border rounded px-3 py-2" placeholder="VAT_STD / VAT_ZERO / VAT_EXEMPT">
        @error('vat_category') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex items-center gap-2 mt-6">
        @php $active = old('is_active', $item->is_active ?? true); @endphp
        <input id="is_active" name="is_active" type="checkbox" value="1" class="h-4 w-4" @checked((bool)$active)>
        <label for="is_active" class="text-sm">Active</label>
        @error('is_active') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>
</div>
