<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalPeriod extends Model
{
    protected $table = 'fiscal_periods';

    protected $casts = [
        'company_id' => 'int',
        'is_closed' => 'bool',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];
}
