@php
    // Safe defaults: create page won't have $invoice
    $invoice ??= null;
    $editing = filled($invoice?->id);

    $oldLines = old('lines');

    // If old() exists, use it. Otherwise:
    // - Edit: use $invoice->lines (collection)
    // - Create: 1 default row
    $lines = $oldLines
        ? collect($oldLines)
        : ($invoice?->lines ?? collect([
            ['item_id'=>'','warehouse_id'=>'','qty'=>1,'rate'=>0,'vat_rate'=>0,'description'=>'']
        ]));
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="text-xs text-slate-300">Customer</label>
        <select name="customer_id"
                class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 placeholder:text-slate-400 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                required>
            <option value="">Select...</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected(old('customer_id', $invoice?->customer_id) == $c->id)>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-xs text-slate-300">Posting Date</label>
        <input type="date" name="posting_date"
               value="{{ old('posting_date', optional($invoice?->posting_date)->format('Y-m-d') ?? now()->toDateString()) }}"
               class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
               required>
    </div>

    <div>
        <label class="text-xs text-slate-300">Due Date</label>
        <input type="date" name="due_date"
               value="{{ old('due_date', optional($invoice?->due_date)->format('Y-m-d')) }}"
               class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
    </div>

    <div>
        <label class="text-xs text-slate-300">Currency</label>
        <input name="currency"
               value="{{ old('currency', $invoice?->currency ?? company_currency()) }}"
               class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
               required>
    </div>

    <div>
        <label class="text-xs text-slate-300">Exchange Rate</label>
        <input name="exchange_rate"
               value="{{ old('exchange_rate', $invoice?->exchange_rate ?? 1) }}"
               class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
    </div>

    <div>
        <label class="text-xs text-slate-300">Remarks</label>
        <input name="remarks"
               value="{{ old('remarks', $invoice?->remarks ?? '') }}"
               class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
    </div>
</div>

<div class="mt-4 rounded-xl ring-1 ring-white/10 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-white/5">
        <tr>
            <th class="p-3 text-left">Item</th>
            <th class="p-3 text-left">Warehouse</th>
            <th class="p-3 text-left">Qty</th>
            <th class="p-3 text-left">Rate</th>
            <th class="p-3 text-left">VAT %</th>
            <th class="p-3 text-left">Description</th>
        </tr>
        </thead>

        <tbody class="divide-y divide-white/10">
        @foreach($lines as $i => $l)
            @php
                // Works whether $l is array (old input) or model (edit)
                $itemId = is_array($l) ? ($l['item_id'] ?? '') : ($l->item_id ?? '');
                $whId   = is_array($l) ? ($l['warehouse_id'] ?? '') : ($l->warehouse_id ?? '');
                $qty    = is_array($l) ? ($l['qty'] ?? 1) : ($l->qty ?? 1);
                $rate   = is_array($l) ? ($l['rate'] ?? 0) : ($l->rate ?? 0);
                $vat    = is_array($l) ? ($l['vat_rate'] ?? 0) : ($l->vat_rate ?? 0);
                $desc   = is_array($l) ? ($l['description'] ?? '') : ($l->description ?? '');
            @endphp

            <tr>
                <td class="p-2">
                    <select name="lines[{{ $i }}][item_id]"
                            class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                            required>
                        <option value="">Select...</option>
                        @foreach($items as $it)
                            <option value="{{ $it->id }}" @selected((string)$itemId === (string)$it->id)>
                                {{ $it->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                <td class="p-2">
                    <select name="lines[{{ $i }}][warehouse_id]"
                            class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                        <option value="">—</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected((string)$whId === (string)$w->id)>
                                {{ $w->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                <td class="p-2">
                    <input name="lines[{{ $i }}][qty]" value="{{ $qty }}"
                           class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                           required>
                </td>

                <td class="p-2">
                    <input name="lines[{{ $i }}][rate]" value="{{ $rate }}"
                           class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                           required>
                </td>

                <td class="p-2">
                    <input name="lines[{{ $i }}][vat_rate]" value="{{ $vat }}"
                           class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                </td>

                <td class="p-2">
                    <input name="lines[{{ $i }}][description]" value="{{ $desc }}"
                           class="w-full px-2 py-2 rounded bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="text-xs text-slate-400 mt-2">
    Tip: Next step we’ll add JS add/remove rows properly (no devtools hacks).
</div>
