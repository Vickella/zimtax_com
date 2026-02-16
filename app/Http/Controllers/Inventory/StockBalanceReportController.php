<?php


namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StockBalanceReportController extends Controller
{
    public function index()
    {
        $companyId = company_id();

        $rows = DB::table('stock_ledger_entries as sle')
            ->join('items as i', 'i.id', '=', 'sle.item_id')
            ->join('warehouses as w', 'w.id', '=', 'sle.warehouse_id')
            ->where('sle.company_id', $companyId)
            ->select([
                'i.id as item_id',
                'i.sku',
                'i.name as item_name',
                'w.id as warehouse_id',
                'w.name as warehouse_name',
                DB::raw('SUM(sle.qty) as qty_on_hand'),
                DB::raw('SUM(COALESCE(sle.value_change,0)) as stock_value_change'),
            ])
            ->groupBy('i.id','i.sku','i.name','w.id','w.name')
            ->havingRaw('SUM(sle.qty) <> 0')
            ->orderBy('i.name')
            ->orderBy('w.name')
            ->get();

        return view('modules.inventory.reports.stock_balance', compact('rows'));
    }
}
