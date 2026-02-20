<?php

namespace App\Models\Tax;

use Illuminate\Database\Eloquent\Model;

class Itf12bPayment extends Model
{
    protected $table = 'itf12b_payments';

    protected $fillable = [
        'company_id','itf12b_projection_id','quarter_no',
        'payment_date','amount','reference',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'float',
    ];
}
