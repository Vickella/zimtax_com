<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Http\Requests\Inventory\StoreItemRequest;
use App\Http\Requests\Inventory\UpdateItemRequest;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::query()
            ->orderBy('name')
            ->paginate(25);

        return view('modules.inventory.items.index', compact('items'));
    }

    public function create()
    {
        return view('modules.inventory.items.create');
    }

    public function store(StoreItemRequest $request)
    {
        Item::create($request->validated());

        return redirect()
            ->route('modules.inventory.items.index')
            ->with('success', 'Item created successfully.');
    }

    public function edit(Item $item)
    {
        return view('modules.inventory.items.edit', compact('item'));
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return redirect()
            ->route('modules.inventory.items.index')
            ->with('success', 'Item updated successfully.');
    }
}
