<?php

namespace App\Services\Payroll;

use App\Services\Accounting\JournalPostingService;
use App\Services\PayrollCalculator;
use App\Models\PayrollRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollPostingService
{
    public function __construct(
        private JournalPostingService $journalService,
        private PayrollCalculator $payrollCalculator
    ) {}

    /**
     * Post payroll to accounting with balanced journal entries
     */
    public function postPayroll(PayrollRun $payrollRun, int $userId): array
    {
        return DB::transaction(function () use ($payrollRun, $userId) {
            // Get account mappings (you should store these in settings)
            $accountMap = $this->getAccountMap($payrollRun->company_id);
            
            // Calculate payroll for all employees
            $payrollData = $this->calculatePayrollData($payrollRun, $accountMap);
            
            // Generate balanced journal lines
            $lines = $this->payrollCalculator->generatePayrollJournalLines(
                $payrollData,
                $accountMap
            );
            
            // Verify lines are balanced before posting
            $totalDebit = collect($lines)->sum('debit');
            $totalCredit = collect($lines)->sum('credit');
            
            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \RuntimeException(
                    "Payroll journal would be unbalanced! " .
                    "Debits: {$totalDebit}, Credits: {$totalCredit}"
                );
            }
            
            // Create and post journal entry
            $journalEntry = $this->journalService->createAndPostJournal(
                $payrollRun->company_id,
                now()->toDateString(),
                "Payroll posting - {$payrollRun->reference}",
                'PayrollRun',
                $payrollRun->id,
                'ZIG',
                1.0,
                $userId,
                $lines
            );
            
            // Update payroll run
            $payrollRun->update([
                'posted_at' => now(),
                'posted_by' => $userId,
                'journal_entry_id' => $journalEntry->id,
                'payroll_data' => json_encode($payrollData) // Store calculated data
            ]);
            
            Log::info('Payroll posted successfully', [
                'payroll_run' => $payrollRun->id,
                'journal_entry' => $journalEntry->entry_no,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit
            ]);
            
            return [
                'success' => true,
                'journal_entry' => $journalEntry,
                'payroll_data' => $payrollData
            ];
        });
    }
    
    /**
     * Calculate payroll data for all employees
     */
    private function calculatePayrollData(PayrollRun $payrollRun, array $accountMap): array
    {
        $totals = [
            'gross_pay' => 0,
            'net_pay' => 0,
            'total_deductions' => 0,
            'employer_costs' => 0,
            'breakdown' => [
                'paye' => 0,
                'aids_levy' => 0,
                'nssa_employee' => 0,
                'nssa_employer' => 0,
                'nec_levy' => 0,
            ]
        ];
        
        // Get all employees in this payroll run
        $employees = $payrollRun->employees ?? [];
        
        foreach ($employees as $employee) {
            $employeeCalc = $this->payrollCalculator->calculateFullPayroll(
                $payrollRun->company_id,
                $employee['id'],
                $employee['gross_pay'],
                $payrollRun->posting_date
            );
            
            $totals['gross_pay'] += $employeeCalc['gross_pay'];
            $totals['net_pay'] += $employeeCalc['net_pay'];
            $totals['total_deductions'] += $employeeCalc['total_deductions'];
            $totals['employer_costs'] += $employeeCalc['employer_costs'];
            
            foreach ($employeeCalc['breakdown'] as $key => $value) {
                $totals['breakdown'][$key] += $value;
            }
        }
        
        // Round all totals
        $totals['gross_pay'] = round($totals['gross_pay'], 2);
        $totals['net_pay'] = round($totals['net_pay'], 2);
        $totals['total_deductions'] = round($totals['total_deductions'], 2);
        $totals['employer_costs'] = round($totals['employer_costs'], 2);
        
        foreach ($totals['breakdown'] as $key => $value) {
            $totals['breakdown'][$key] = round($value, 2);
        }
        
        return $totals;
    }
    
    /**
     * Get account mappings (you should store these in database)
     */
    private function getAccountMap(int $companyId): array
    {
        // TODO: Load these from settings table
        return [
            'salary_expense' => 6100,
            'paye_payable' => 2100,
            'aids_payable' => 2101,
            'nssa_payable' => 2200,
            'nec_payable' => 2201,
            'other_deductions_payable' => 2202,
            'bank_account' => 1000,
            'employer_cost_expense' => 6200,
            'suspense_account' => 9999,
        ];
    }
    
    /**
     * Fix existing unbalanced payroll entries
     */
    public function fixUnbalancedPayrollEntries(int $companyId, int $userId): array
    {
        $stats = [
            'fixed' => 0,
            'failed' => 0,
            'entries' => []
        ];
        
        $entries = \App\Models\JournalEntry::where('company_id', $companyId)
            ->where('source_type', 'PayrollRun')
            ->where('status', 'POSTED')
            ->whereDoesntHave('glEntries')
            ->with('lines')
            ->get();
        
        foreach ($entries as $je) {
            try {
                DB::transaction(function () use ($je, $userId, &$stats) {
                    $dr = $je->lines->sum('debit');
                    $cr = $je->lines->sum('credit');
                    $diff = round($dr - $cr, 2);
                    
                    // Create GL entries for each line
                    foreach ($je->lines as $line) {
                        \App\Models\GLEntry::updateOrCreate(
                            [
                                'journal_entry_id' => $je->id,
                                'journal_line_id' => $line->id
                            ],
                            [
                                'company_id' => $je->company_id,
                                'posting_date' => $je->posting_date,
                                'account_id' => $line->account_id,
                                'debit' => $line->debit,
                                'credit' => $line->credit,
                                'currency' => $je->currency,
                                'amount_base' => ($line->debit - $line->credit) * ($je->exchange_rate ?? 1),
                                'party_type' => $line->party_type,
                                'party_id' => $line->party_id,
                                'created_by' => $userId,
                            ]
                        );
                    }
                    
                    $stats['fixed']++;
                    $stats['entries'][] = [
                        'entry_no' => $je->entry_no,
                        'difference' => $diff,
                        'status' => 'fixed'
                    ];
                });
            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['entries'][] = [
                    'entry_no' => $je->entry_no,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $stats;
    }
}