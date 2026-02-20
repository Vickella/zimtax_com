<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $table = 'payslips';
    public $timestamps = false;

    protected $fillable = [
        'payroll_run_id','employee_id',
        'gross_pay','taxable_pay','paye','aids_levy',
        'nssa_employee','nssa_employer','zimdef_employee','zimdef_employer',
        'total_deductions','net_pay'
    ];

    public function lines()
    {
        return $this->hasMany(PayslipLine::class, 'payslip_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
