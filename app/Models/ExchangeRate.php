<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';
    public $timestamps = false;

    protected $fillable = [
        'company_id','base_currency','quote_currency','rate','rate_date','source'
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'rate_date' => 'date',
    ];
}
