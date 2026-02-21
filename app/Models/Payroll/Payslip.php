<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $table = 'payslips';
    
    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'gross_pay',
        'taxable_pay',
        'paye',
        'aids_levy',
        'nssa_employee',
        'nssa_employer',
        'nec_levy',
        'zimdef_employee',
        'zimdef_employer',
        'total_deductions',
        'net_pay',
    ];

    protected $casts = [
        'gross_pay' => 'decimal:2',
        'taxable_pay' => 'decimal:2',
        'paye' => 'decimal:2',
        'aids_levy' => 'decimal:2',
        'nssa_employee' => 'decimal:2',
        'nssa_employer' => 'decimal:2',
        'nec_levy' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    /**
     * Get the payroll run that owns this payslip
     */
    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    /**
     * Get the employee that owns this payslip
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the lines for this payslip
     */
    public function lines()
    {
        return $this->hasMany(PayslipLine::class, 'payslip_id');
    }
}