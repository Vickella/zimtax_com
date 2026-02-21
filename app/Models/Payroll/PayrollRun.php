<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'submitted_at',
        'submitted_by',
        'gl_journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'exchange_rate' => 'decimal:2',
    ];

    /**
     * Get the payslips for this payroll run
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'payroll_run_id');
    }

    /**
     * Get the period for this payroll run
     */
    public function period()
    {
        return $this->belongsTo(FiscalPeriod::class, 'period_id');
    }

    /**
     * Get the company for this payroll run
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created this run
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who submitted this run
     */
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}