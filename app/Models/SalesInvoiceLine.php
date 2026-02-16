<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceLine extends Model
{
    protected $table = 'sales_invoice_lines';

    public $timestamps = false;

    protected $fillable = [
        'sales_invoice_id','item_id','description','warehouse_id',
        'qty','rate','amount','vat_rate','vat_amount'
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'rate' => 'decimal:6',
        'amount' => 'decimal:2',
        'vat_rate' => 'decimal:4',
        'vat_amount' => 'decimal:2',
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
