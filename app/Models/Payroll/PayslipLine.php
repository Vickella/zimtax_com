<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayslipLine extends Model
{
    protected $table = 'payslip_lines';
    public $timestamps = false;

    protected $fillable = ['payslip_id','payroll_component_id','amount'];
}
