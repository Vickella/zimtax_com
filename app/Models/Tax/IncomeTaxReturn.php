<?php

namespace App\Models\Tax;

use Illuminate\Database\Eloquent\Model;

class IncomeTaxReturn extends Model
{
    protected $table = 'income_tax_returns';

    protected $fillable = [
        'company_id',
        'tax_year',
        'period_start',
        'period_end',
        'income_tax_rate',
        'profit_before_tax',
        'add_backs','deductions',
        'taxable_income',
        'income_tax_payable',
        'notes',
        'source_snapshot',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'income_tax_rate' => 'float',
        'profit_before_tax' => 'float',
        'add_backs' => 'float',
        'deductions' => 'float',
        'taxable_income' => 'float',
        'income_tax_payable' => 'float',
        'source_snapshot' => 'array',
    ];
}