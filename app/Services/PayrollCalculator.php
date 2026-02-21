<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollCalculator
{
    public function statutoryForEmployee(int $companyId, int $employeeId, float $taxablePay, string $postingDate): array
    {
        $settings = $this->getStatutorySettings($companyId, $postingDate);
        
        // Log settings for debugging
        Log::info('Payroll settings retrieved', [
            'company_id' => $companyId,
            'posting_date' => $postingDate,
            'settings' => $settings
        ]);
        
        $paye = $this->calculatePaye($companyId, $taxablePay, $postingDate);

        $aids = round($paye * (float)$settings['aids_levy_rate'], 2);

        // NSSA uses ceiling base
        $nssaBase = min($taxablePay, (float)$settings['nssa_ceiling_amount']);
        $nssaEmployee = round($nssaBase * (float)$settings['nssa_employee_rate'], 2);
        $nssaEmployer = round($nssaBase * (float)$settings['nssa_employer_rate'], 2);

        // NEC rate from metadata JSON - FIXED
        $necRate = (float)($settings['nec_rate'] ?? 0);
        $nec = round($taxablePay * $necRate, 2);
        
        Log::info('NEC calculation', [
            'taxable_pay' => $taxablePay,
            'nec_rate' => $necRate,
            'nec_amount' => $nec
        ]);

        return [
            'paye'          => $paye,
            'aids_levy'     => $aids,
            'nssa_employee' => $nssaEmployee,
            'nssa_employer' => $nssaEmployer,
            'nec_levy'      => $nec,
        ];
    }

    private function getStatutorySettings(int $companyId, string $postingDate): array
    {
        $row = DB::table('payroll_statutory_settings')
            ->where('company_id', $companyId)
            ->where('effective_from', '<=', $postingDate)
            ->where(function ($q) use ($postingDate) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $postingDate);
            })
            ->orderByDesc('effective_from')
            ->first();

        if (!$row) {
            Log::warning('No statutory settings found', [
                'company_id' => $companyId,
                'posting_date' => $postingDate
            ]);
            
            return [
                'nssa_employee_rate' => 0,
                'nssa_employer_rate' => 0,
                'nssa_ceiling_amount'=> 0,
                'aids_levy_rate'     => 0,
                'nec_rate'           => 0,
            ];
        }

        // Decode metadata JSON - NEC rate is stored here
        $meta = [];
        if (!empty($row->metadata)) {
            $meta = json_decode($row->metadata, true) ?: [];
        }

        return [
            'nssa_employee_rate' => (float)$row->nssa_employee_rate,
            'nssa_employer_rate' => (float)$row->nssa_employer_rate,
            'nssa_ceiling_amount'=> (float)$row->nssa_ceiling_amount,
            'aids_levy_rate'     => (float)$row->aids_levy_rate,
            'nec_rate'           => (float)($meta['nec_rate'] ?? 0), // FIXED: Extract from metadata
        ];
    }

    private function calculatePaye(int $companyId, float $taxablePay, string $postingDate): float
    {
        $bands = DB::table('paye_brackets')
            ->where('company_id', $companyId)
            ->where('effective_from', '<=', $postingDate)
            ->where(function ($q) use ($postingDate) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $postingDate);
            })
            ->orderBy('band_order')
            ->get();

        if ($bands->isEmpty()) {
            Log::warning('No PAYE brackets found', [
                'company_id' => $companyId,
                'posting_date' => $postingDate
            ]);
            return 0;
        }

        foreach ($bands as $b) {
            $lower = (float)$b->lower_bound;
            $upper = $b->upper_bound !== null ? (float)$b->upper_bound : null;

            if ($taxablePay >= $lower && ($upper === null || $taxablePay <= $upper)) {
                $rate = (float)$b->rate;
                $baseTax = (float)$b->base_tax;
                $paye = $baseTax + (($taxablePay - $lower) * $rate);
                return round(max(0, $paye), 2);
            }
        }

        return 0;
    }

    /**
     * Calculate complete payroll breakdown including all statutory deductions
     */
    public function calculateFullPayroll(int $companyId, int $employeeId, float $grossPay, string $postingDate): array
    {
        $statutory = $this->statutoryForEmployee($companyId, $employeeId, $grossPay, $postingDate);
        
        // Calculate totals
        $totalDeductions = $statutory['paye'] + $statutory['aids_levy'] + 
                          $statutory['nssa_employee'] + $statutory['nec_levy'];
        
        $netPay = $grossPay - $totalDeductions;
        
        // Employer costs
        $employerCosts = $statutory['nssa_employer']; // Add other employer costs as needed
        
        return [
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'total_deductions' => $totalDeductions,
            'employer_costs' => $employerCosts,
            'total_cost' => $grossPay + $employerCosts,
            'breakdown' => $statutory
        ];
    }

    /**
     * Generate balanced journal lines for payroll
     */
    public function generatePayrollJournalLines(array $payrollData, array $accountMap): array
    {
        $lines = [];
        $totalDebit = 0;
        $totalCredit = 0;
        
        // 1. Gross Salary (DEBIT - Expense)
        $lines[] = [
            'account_id' => $accountMap['salary_expense'],
            'description' => 'Gross salaries',
            'debit' => $payrollData['gross_pay'],
            'credit' => 0,
            'party_type' => 'NONE',
        ];
        $totalDebit += $payrollData['gross_pay'];
        
        // 2. PAYE (CREDIT - Liability)
        if ($payrollData['breakdown']['paye'] > 0) {
            $lines[] = [
                'account_id' => $accountMap['paye_payable'],
                'description' => 'PAYE tax',
                'debit' => 0,
                'credit' => $payrollData['breakdown']['paye'],
                'party_type' => 'NONE',
            ];
            $totalCredit += $payrollData['breakdown']['paye'];
        }
        
        // 3. AIDS Levy (CREDIT - Liability)
        if ($payrollData['breakdown']['aids_levy'] > 0) {
            $lines[] = [
                'account_id' => $accountMap['aids_payable'],
                'description' => 'AIDS levy',
                'debit' => 0,
                'credit' => $payrollData['breakdown']['aids_levy'],
                'party_type' => 'NONE',
            ];
            $totalCredit += $payrollData['breakdown']['aids_levy'];
        }
        
        // 4. NSSA Employee (CREDIT - Liability)
        if ($payrollData['breakdown']['nssa_employee'] > 0) {
            $lines[] = [
                'account_id' => $accountMap['nssa_payable'],
                'description' => 'NSSA employee contribution',
                'debit' => 0,
                'credit' => $payrollData['breakdown']['nssa_employee'],
                'party_type' => 'NONE',
            ];
            $totalCredit += $payrollData['breakdown']['nssa_employee'];
        }
        
        // 5. NEC Levy (CREDIT - Liability) - FIXED: Now properly included
        if ($payrollData['breakdown']['nec_levy'] > 0) {
            $lines[] = [
                'account_id' => $accountMap['nec_payable'] ?? $accountMap['other_deductions_payable'],
                'description' => 'NEC levy',
                'debit' => 0,
                'credit' => $payrollData['breakdown']['nec_levy'],
                'party_type' => 'NONE',
            ];
            $totalCredit += $payrollData['breakdown']['nec_levy'];
        }
        
        // 6. Net Pay (CREDIT - Bank/Cash)
        $lines[] = [
            'account_id' => $accountMap['bank_account'],
            'description' => 'Net pay',
            'debit' => 0,
            'credit' => $payrollData['net_pay'],
            'party_type' => 'NONE',
        ];
        $totalCredit += $payrollData['net_pay'];
        
        // 7. Employer NSSA (DEBIT - Expense)
        if ($payrollData['employer_costs'] > 0) {
            $lines[] = [
                'account_id' => $accountMap['employer_cost_expense'],
                'description' => 'Employer NSSA contribution',
                'debit' => $payrollData['employer_costs'],
                'credit' => 0,
                'party_type' => 'NONE',
            ];
            $totalDebit += $payrollData['employer_costs'];
            
            // Add corresponding liability for employer costs
            $lines[] = [
                'account_id' => $accountMap['nssa_payable'],
                'description' => 'Employer NSSA contribution payable',
                'debit' => 0,
                'credit' => $payrollData['employer_costs'],
                'party_type' => 'NONE',
            ];
            $totalCredit += $payrollData['employer_costs'];
        }
        
        // VERIFY BALANCE
        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);
        
        Log::info('Payroll journal totals', [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => $totalDebit - $totalCredit
        ]);
        
        // Handle any rounding differences (should be 0 if calculations are correct)
        $difference = round($totalDebit - $totalCredit, 2);
        
        if (abs($difference) > 0.01) {
            // Significant difference - log error
            Log::error('Payroll journal unbalanced!', [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'difference' => $difference
            ]);
            
            // Add to suspense account
            $suspenseAccountId = $accountMap['suspense_account'] ?? 9999;
            
            if ($difference > 0) {
                // Need more credit
                $lines[] = [
                    'account_id' => $suspenseAccountId,
                    'description' => 'Payroll rounding adjustment',
                    'debit' => 0,
                    'credit' => $difference,
                    'party_type' => 'NONE',
                ];
            } else {
                // Need more debit
                $lines[] = [
                    'account_id' => $suspenseAccountId,
                    'description' => 'Payroll rounding adjustment',
                    'debit' => abs($difference),
                    'credit' => 0,
                    'party_type' => 'NONE',
                ];
            }
        } elseif (abs($difference) > 0) {
            // Tiny rounding difference - adjust the last line
            $lastLine = array_pop($lines);
            if ($lastLine['credit'] > 0) {
                $lastLine['credit'] = round($lastLine['credit'] + $difference, 2);
            } else {
                $lastLine['debit'] = round($lastLine['debit'] - $difference, 2);
            }
            $lines[] = $lastLine;
            
            Log::info('Applied rounding adjustment', ['difference' => $difference]);
        }
        
        return $lines;
    }
}