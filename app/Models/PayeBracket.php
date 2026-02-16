<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayeBracket extends Model
{
    protected $table = 'paye_brackets';
    public $timestamps = false;

    protected $fillable = [
        'company_id','effective_from','effective_to','band_order',
        'lower_bound','upper_bound','rate','base_tax'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'lower_bound' => 'decimal:2',
        'upper_bound' => 'decimal:2',
        'rate' => 'decimal:4',
        'base_tax' => 'decimal:2',
    ];
}
