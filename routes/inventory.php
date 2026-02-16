<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventory\InventoryModuleController;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\StockLedgerController;


Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryModuleController::class, 'index'])->name('index');

    // Items
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('items', [ItemController::class, 'store'])->name('items.store');
    Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');

    // Warehouses
    Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');

    // Stock Ledger (read-only)
    Route::get('stock-ledger', [StockLedgerController::class, 'index'])->name('stock-ledger.index');
});

require __DIR__.'/inventory.php';