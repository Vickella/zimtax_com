<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class StockLedgerEntry extends Model
{
    use BelongsToCompany;

    protected $table = 'stock_ledger_entries';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'item_id',
        'warehouse_id',
        'posting_date',
        'posting_time',
        'voucher_type',
        'voucher_id',
        'qty',
        'unit_cost',
        'value_change',
        'running_qty',
        'created_at',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'qty' => 'float',
        'unit_cost' => 'float',
        'value_change' => 'float',
        'running_qty' => 'float',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
