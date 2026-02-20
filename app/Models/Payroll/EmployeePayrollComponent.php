<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payroll\PayrollComponent;
use App\Models\Payroll\Employee;

class EmployeePayrollComponent extends Model
{
    protected $table = 'employee_payroll_components';

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'payroll_component_id',
        'amount',
        'formula',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function component()
    {
        return $this->belongsTo(PayrollComponent::class, 'payroll_component_id');
    }
}
