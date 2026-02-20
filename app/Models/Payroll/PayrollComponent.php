<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollComponent extends Model
{
    protected $table = 'payroll_components';

    protected $fillable = [
        'company_id',
        'name',
        'component_type',   // EARNING|DEDUCTION
        'taxable',
        'affects_nssa',
        'affects_paye',
        'is_loan_component',
        'created_at',
    ];

    protected $casts = [
        'taxable' => 'boolean',
        'affects_nssa' => 'boolean',
        'affects_paye' => 'boolean',
        'is_loan_component' => 'boolean',
    ];
}
