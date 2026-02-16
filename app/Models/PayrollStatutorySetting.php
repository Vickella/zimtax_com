<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollStatutorySetting extends Model
{
    protected $table = 'payroll_statutory_settings';
    public $timestamps = false;

    protected $fillable = [
        'company_id','effective_from','effective_to',
        'nssa_employee_rate','nssa_employer_rate','nssa_ceiling_amount',
        'aids_levy_rate','zimdef_employee_rate','zimdef_employer_rate','metadata'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'metadata' => 'array',
        'nssa_employee_rate' => 'decimal:4',
        'nssa_employer_rate' => 'decimal:4',
        'aids_levy_rate' => 'decimal:4',
        'nssa_ceiling_amount' => 'decimal:2',
        'zimdef_employee_rate' => 'decimal:4',
        'zimdef_employer_rate' => 'decimal:4',
    ];
}
