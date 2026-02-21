<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll\PayrollRun;
use App\Services\PayrollCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixPayrollNEC extends Command
{
    protected $signature = 'payroll:fix-nec {run?}';
    protected $description = 'Fix NEC calculations for payroll runs';

    public function handle(PayrollCalculator $calculator)
    {
        $query = PayrollRun::query();
        
        if ($runId = $this->argument('run')) {
            $query->where('id', $runId);
        }
        
        $runs = $query->get();
        
        if ($runs->isEmpty()) {
            $this->error('No payroll runs found.');
            return 1;
        }
        
        foreach ($runs as $run) {
            $this->info("Processing run: {$run->run_no} (ID: {$run->id})");
            
            // Load payslips relationship
            $run->load('payslips');
            
            if ($run->payslips->isEmpty()) {
                $this->warn("  No payslips found for this run.");
                continue;
            }
            
            $period = DB::table('fiscal_periods')->where('id', $run->period_id)->first();
            
            if (!$period) {
                $this->error("  Period not found for ID: {$run->period_id}");
                continue;
            }
            
            $fixed = 0;
            
            foreach ($run->payslips as $payslip) {
                try {
                    // Log current values
                    $this->line("  Processing employee ID: {$payslip->employee_id}");
                    $this->line("    Current - Gross: {$payslip->gross_pay}, Taxable: {$payslip->taxable_pay}");
                    
                    $stat = $calculator->statutoryForEmployee(
                        $run->company_id,
                        $payslip->employee_id,
                        $payslip->taxable_pay,
                        $period->end_date
                    );
                    
                    $this->line("    Calculated - PAYE: {$stat['paye']}, AIDS: {$stat['aids_levy']}, NSSA: {$stat['nssa_employee']}, NEC: {$stat['nec_levy']}");
                    
                    $updates = [];
                    
                    // Check if NEC needs update
                    if (abs($payslip->nec_levy - $stat['nec_levy']) > 0.01) {
                        $updates['nec_levy'] = $stat['nec_levy'];
                        $this->line("    → NEC will be updated from {$payslip->nec_levy} to {$stat['nec_levy']}");
                    }
                    
                    // Recalculate total deductions
                    $newTotalDeductions = $stat['paye'] + $stat['aids_levy'] + $stat['nssa_employee'] + $stat['nec_levy'];
                    if (abs($payslip->total_deductions - $newTotalDeductions) > 0.01) {
                        $updates['total_deductions'] = $newTotalDeductions;
                        $this->line("    → Total deductions will be updated from {$payslip->total_deductions} to {$newTotalDeductions}");
                    }
                    
                    // Recalculate net pay
                    $newNetPay = $payslip->gross_pay - $newTotalDeductions;
                    if (abs($payslip->net_pay - $newNetPay) > 0.01) {
                        $updates['net_pay'] = $newNetPay;
                        $this->line("    → Net pay will be updated from {$payslip->net_pay} to {$newNetPay}");
                    }
                    
                    // Apply updates if any
                    if (!empty($updates)) {
                        DB::table('payslips')
                            ->where('id', $payslip->id)
                            ->update($updates);
                        $fixed++;
                        $this->info("    ✅ Updated " . implode(', ', array_keys($updates)));
                    } else {
                        $this->line("    ✓ No updates needed");
                    }
                    
                } catch (\Exception $e) {
                    $this->error("    ❌ Error: " . $e->getMessage());
                    Log::error("Failed to fix payslip {$payslip->id}: " . $e->getMessage());
                }
            }
            
            $this->info("  Fixed {$fixed} payslips in run {$run->run_no}\n");
        }
        
        $this->info('✅ NEC fix completed!');
        return 0;
    }
}