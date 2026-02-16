{{-- resources/views/modules/inventory/warehouses/_form.blade.php --}}
@php
    $isEdit = isset($warehouse);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Code <span class="text-red-600">*</span></label>
        <input name="code" value="{{ old('code', $warehouse->code ?? '') }}" class="w-full border rounded px-3 py-2" required>
        @error('code') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
        <input name="name" value="{{ old('name', $warehouse->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
        @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Location</label>
        <input name="location" value="{{ old('location', $warehouse->location ?? '') }}" class="w-full border rounded px-3 py-2">
        @error('location') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex items-center gap-2 mt-2">
        @php $active = old('is_active', $warehouse->is_active ?? true); @endphp
        <input id="is_active" name="is_active" type="checkbox" value="1" class="h-4 w-4" @checked((bool)$active)>
        <label for="is_active" class="text-sm">Active</label>
        @error('is_active') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>
</div>
