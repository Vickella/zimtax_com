<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'code',
        'name',
        'trading_name',
        'tin',
        'vat_number',
        'address',
        'phone',
        'email',
        'base_currency',
        'fiscal_year_start_month',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fiscal_year_start_month' => 'integer',
    ];
}
