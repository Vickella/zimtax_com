<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'company_id',
        'employee_no',
        'first_name',
        'last_name',
        'national_id',
        'tin',
        'nssa_number',
        'nec',
        'bank_name',
        'bank_account_number',
        'currency',
        'hire_date',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];
}
