<?php

namespace App\Models\Tax;

use Illuminate\Database\Eloquent\Model;

class VatReturn extends Model
{
    protected $table = 'vat_returns';

    protected $fillable = [
        'company_id','period_start','period_end',
        'vat_rate',
        'taxable_sales','output_vat',
        'taxable_purchases','input_vat',
        'net_vat_payable',
        'notes',
        'source_snapshot',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'vat_rate' => 'float',
        'taxable_sales' => 'float',
        'output_vat' => 'float',
        'taxable_purchases' => 'float',
        'input_vat' => 'float',
        'net_vat_payable' => 'float',
        'source_snapshot' => 'array',
    ];
}
