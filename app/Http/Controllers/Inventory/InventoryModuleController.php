<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\StockLedgerEntry;

class InventoryModuleController extends Controller
{
    public function index()
    {
        $stats = [
            'items' => Item::active()->count(),
            'warehouses' => Warehouse::active()->count(),
            'ledger_entries_today' => StockLedgerEntry::whereDate('posting_date', now()->toDateString())->count(),
        ];

        return view('modules.inventory.index', compact('stats'));
    }
}
