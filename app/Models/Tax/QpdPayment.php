<?php

namespace App\Models\Tax;

use Illuminate\Database\Eloquent\Model;

class QpdPayment extends Model
{
    protected $table = 'qpd_payments';

    protected $fillable = [
        'qpd_forecast_id','quarter_no','due_date',
        'quarter_percent','cumulative_percent',
        'cumulative_due_amount','amount_already_paid','amount_now_due',
        'payment_date','reference','notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
    ];
}
