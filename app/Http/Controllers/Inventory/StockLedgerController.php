<?php


namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\StockLedgerEntry;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        $companyId = company_id(); // your helper

        $itemId = $request->integer('item_id') ?: null;
        $warehouseId = $request->integer('warehouse_id') ?: null;
        $from = $request->date('from');
        $to = $request->date('to');
        $voucherType = $request->string('voucher_type')->toString();

        $query = StockLedgerEntry::query()
            ->where('company_id', $companyId);

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($from) {
            $query->whereDate('posting_date', '>=', $from->format('Y-m-d'));
        }

        if ($to) {
            $query->whereDate('posting_date', '<=', $to->format('Y-m-d'));
        }

        if (!empty($voucherType) && $voucherType !== 'All') {
            $query->where('voucher_type', $voucherType);
        }

        // Stock balance using window function (MySQL 8+)
        // Balance is running SUM(qty) per item+warehouse over time.
        $query->select([
            'stock_ledger_entries.*',
        ])->selectRaw(
            "SUM(qty) OVER (
                PARTITION BY company_id, item_id, warehouse_id
                ORDER BY posting_date ASC, posting_time ASC, id ASC
            ) AS stock_balance"
        );

        // Display newest first (but balance still correct because it was computed on ASC window ordering)
        $entries = $query
            ->with([
                'item:id,name,sku',
                'warehouse:id,name',
            ])
            ->orderBy('posting_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        $items = Item::query()
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        $voucherTypes = StockLedgerEntry::query()
            ->where('company_id', $companyId)
            ->select('voucher_type')
            ->distinct()
            ->orderBy('voucher_type')
            ->pluck('voucher_type');

        return view('modules.inventory.ledger.index', [
            'entries' => $entries,
            'items' => $items,
            'warehouses' => $warehouses,
            'voucherTypes' => $voucherTypes,
        ]);
    }
}
