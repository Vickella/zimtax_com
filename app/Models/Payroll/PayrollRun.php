<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $table = 'payroll_runs';

    protected $fillable = [
        'company_id',
        'run_no',
        'period_id',
        'currency',
        'exchange_rate',
        'status',
        'processed_at',
        'created_by',
        'submitted_by',
        'submitted_at',
        'gl_journal_entry_id',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];
}
