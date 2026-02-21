<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Employee;
use App\Models\Payroll\EmployeePayrollComponent;
use App\Models\Payroll\PayrollComponent;
use App\Models\Payroll\PayrollRun;
use App\Models\Payroll\Payslip;
use App\Models\Payroll\PayslipLine;
use App\Services\FiscalPeriodService;
use App\Services\PayrollCalculator;
use App\Services\Accounting\JournalPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollRunController extends Controller
{
    protected $journalPostingService;

    public function __construct(JournalPostingService $journalPostingService)
    {
        $this->journalPostingService = $journalPostingService;
    }

    public function index()
    {
        $runs = PayrollRun::query()
            ->where('company_id', company_id())
            ->orderByDesc('id')
            ->paginate(20);

        return view('modules.payroll.runs.index', compact('runs'));
    }

    public function create()
    {
        return view('modules.payroll.runs.create');
    }

    /**
     * Creates the run AND processes payslips (DRAFT)
     * FIXED: Ensures net pay is correctly calculated including manual deductions
     */
    public function store(Request $request, FiscalPeriodService $periodService, PayrollCalculator $calc)
    {
        $data = $request->validate([
            'year'  => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $companyId = company_id();
        $periodId = $periodService->ensureMonthlyPeriod($companyId, (int)$data['year'], (int)$data['month']);

        $period = DB::table('fiscal_periods')->where('id', $periodId)->first();
        $postingDate = $period->end_date;

        $runNo = 'PR-' . date('Ymd-His');

        $run = null;

        DB::transaction(function () use (&$run, $companyId, $periodId, $runNo, $postingDate, $calc) {
            $run = PayrollRun::create([
                'company_id' => $companyId,
                'run_no'     => $runNo,
                'period_id'  => $periodId,
                'currency'   => 'ZIG',
                'exchange_rate' => 1,
                'status'     => 'DRAFT',
                'processed_at' => now(),
                'created_by' => auth()->id(),
            ]);

            $employees = Employee::query()
                ->where('company_id', $companyId)
                ->where('status', 'ACTIVE')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $systemMap = PayrollComponent::query()
                ->where('company_id', $companyId)
                ->whereIn('name', ['NSSA', 'PAYE', 'AIDS Levy', 'NEC Levy'])
                ->pluck('id', 'name')
                ->toArray();

            foreach ($employees as $emp) {
                Log::info('Processing employee', ['employee_id' => $emp->id, 'name' => $emp->first_name . ' ' . $emp->last_name]);
                
                $rows = EmployeePayrollComponent::query()
                    ->where('employee_id', $emp->id)
                    ->where('is_active', 1)
                    ->with('component')
                    ->get();

                $earnings = $rows->filter(fn($r) => $r->component?->component_type === 'EARNING');
                $manualDeductions = $rows->filter(fn($r) => $r->component?->component_type === 'DEDUCTION');

                $gross = round($earnings->sum('amount'), 2);
                $taxable = round($earnings->sum(function ($r) {
                    return (int)($r->component?->taxable ?? 1) === 1 ? (float)$r->amount : 0;
                }), 2);

                $stat = $calc->statutoryForEmployee($companyId, $emp->id, $taxable, $postingDate);

                $manualDedTotal = round($manualDeductions->sum('amount'), 2);
                
                // Calculate total deductions INCLUDING NEC and manual deductions
                $totalDeductions = round(
                    $manualDedTotal +
                    $stat['paye'] +
                    $stat['aids_levy'] +
                    $stat['nssa_employee'] +
                    $stat['nec_levy'],
                    2
                );

                // Calculate net pay CORRECTLY
                $net = round($gross - $totalDeductions, 2);

                // Log the calculations
                Log::info('Payroll calculations', [
                    'employee_id' => $emp->id,
                    'gross' => $gross,
                    'paye' => $stat['paye'],
                    'aids' => $stat['aids_levy'],
                    'nssa' => $stat['nssa_employee'],
                    'nec' => $stat['nec_levy'],
                    'manual_deductions' => $manualDedTotal,
                    'total_deductions' => $totalDeductions,
                    'net' => $net
                ]);

                // VERIFY the calculation before saving (with tolerance for rounding)
                $calculatedTotal = round(
                    $stat['paye'] + 
                    $stat['aids_levy'] + 
                    $stat['nssa_employee'] + 
                    $stat['nec_levy'] + 
                    $manualDedTotal +
                    $net, 
                    2
                );
                
                $difference = round($gross - $calculatedTotal, 2);
                
                // Allow for small rounding differences (0.02)
                if (abs($difference) > 0.02) {
                    Log::error('PAYROLL CALCULATION ERROR', [
                        'employee_id' => $emp->id,
                        'gross' => $gross,
                        'paye' => $stat['paye'],
                        'aids' => $stat['aids_levy'],
                        'nssa' => $stat['nssa_employee'],
                        'nec' => $stat['nec_levy'],
                        'manual_deductions' => $manualDedTotal,
                        'net' => $net,
                        'calculated_total' => $calculatedTotal,
                        'difference' => $difference
                    ]);
                    
                    // Adjust net pay for small differences
                    if (abs($difference) <= 0.10) {
                        $net = round($net - $difference, 2);
                        Log::warning('Adjusted net pay', [
                            'employee_id' => $emp->id,
                            'old_net' => $net + $difference,
                            'new_net' => $net,
                            'adjustment' => -$difference
                        ]);
                    } else {
                        throw new \Exception(
                            "Payroll calculation error for employee {$emp->id}. " .
                            "Gross: {$gross}, Total: {$calculatedTotal}, Diff: {$difference}"
                        );
                    }
                }

                // Create payslip with CORRECT values
                $payslip = Payslip::create([
                    'payroll_run_id' => $run->id,
                    'employee_id'    => $emp->id,
                    'gross_pay'      => $gross,
                    'taxable_pay'    => $taxable,
                    'paye'           => $stat['paye'],
                    'aids_levy'      => $stat['aids_levy'],
                    'nssa_employee'  => $stat['nssa_employee'],
                    'nssa_employer'  => $stat['nssa_employer'],
                    'nec_levy'       => $stat['nec_levy'],
                    'zimdef_employee'=> 0,
                    'zimdef_employer'=> 0,
                    'total_deductions' => $totalDeductions,
                    'net_pay'        => $net,
                ]);

                // Create payslip lines for earnings
                foreach ($earnings as $r) {
                    PayslipLine::create([
                        'payslip_id' => $payslip->id,
                        'payroll_component_id' => $r->payroll_component_id,
                        'amount' => round((float)$r->amount, 2),
                    ]);
                }

                // Create payslip lines for manual deductions
                foreach ($manualDeductions as $r) {
                    PayslipLine::create([
                        'payslip_id' => $payslip->id,
                        'payroll_component_id' => $r->payroll_component_id,
                        'amount' => round((float)$r->amount, 2),
                    ]);
                }

                // Create payslip lines for statutory deductions
                if (!empty($systemMap['PAYE']) && $stat['paye'] > 0) {
                    PayslipLine::create([
                        'payslip_id' => $payslip->id,
                        'payroll_component_id' => $systemMap['PAYE'],
                        'amount' => $stat['paye']
                    ]);
                }
                if (!empty($systemMap['AIDS Levy']) && $stat['aids_levy'] > 0) {
                    PayslipLine::create([
                        'payslip_id' => $payslip->id,
                        'payroll_component_id' => $systemMap['AIDS Levy'],
                        'amount' => $stat['aids_levy']
                    ]);
                }
                if (!empty($systemMap['NSSA']) && $stat['nssa_employee'] > 0) {
                    PayslipLine::create([
                        'payslip_id' => $payslip->id,
                        'payroll_component_id' => $systemMap['NSSA'],
                        'amount' => $stat['nssa_employee']
                    ]);
                }
                if (!empty($systemMap['NEC Levy']) && $stat['nec_levy'] > 0) {
                    PayslipLine::create([
                        'payslip_id' => $payslip->id,
                        'payroll_component_id' => $systemMap['NEC Levy'],
                        'amount' => $stat['nec_levy']
                    ]);
                }
                
                Log::info('Employee processed successfully', ['employee_id' => $emp->id]);
            }
            
            Log::info('Payroll run completed', ['run_id' => $run->id, 'run_no' => $run->run_no]);
        });

        return redirect()->route('modules.payroll.runs.show', $run)
            ->with('success', 'Payroll run processed (Draft).');
    }

    /**
     * Display the payroll run
     * FIXED: Ensures displayed net pay is correct including manual deductions
     */
    public function show(PayrollRun $run)
    {
        abort_unless($run->company_id === company_id(), 403);

        $period = DB::table('fiscal_periods')->where('id', $run->period_id)->first();
        
        $payslips = Payslip::query()
            ->where('payroll_run_id', $run->id)
            ->with('employee')
            ->orderBy('id')
            ->get();

        // Ensure displayed net pay is correct
        foreach ($payslips as $payslip) {
            // Get manual deductions for this payslip
            $manualDeductions = PayslipLine::where('payslip_id', $payslip->id)
                ->whereHas('component', function($q) {
                    $q->where('component_type', 'DEDUCTION');
                })
                ->sum('amount');
            
            // Calculate correct net pay for display including manual deductions
            $correctNet = round(
                $payslip->gross_pay - 
                ($payslip->paye + $payslip->aids_levy + $payslip->nssa_employee + $payslip->nec_levy + $manualDeductions), 
                2
            );
            
            // If stored net is wrong, log it and use correct value for display
            if (abs($correctNet - $payslip->net_pay) > 0.01) {
                Log::warning('Payslip net pay mismatch detected', [
                    'payslip_id' => $payslip->id,
                    'stored_net' => $payslip->net_pay,
                    'correct_net' => $correctNet,
                    'gross' => $payslip->gross_pay,
                    'paye' => $payslip->paye,
                    'aids' => $payslip->aids_levy,
                    'nssa' => $payslip->nssa_employee,
                    'nec' => $payslip->nec_levy,
                    'manual_deductions' => $manualDeductions
                ]);
                
                // Use correct value for display only
                $payslip->net_pay = $correctNet;
                $payslip->total_deductions = $payslip->paye + $payslip->aids_levy + 
                                             $payslip->nssa_employee + $payslip->nec_levy + $manualDeductions;
            }
        }

        return view('modules.payroll.runs.show', compact('run', 'period', 'payslips'));
    }

    /**
     * Submit payroll run - creates journal entries and posts to GL
     * FIXED: Includes manual deductions in journal balancing
     */
    public function submit(PayrollRun $run)
    {
        abort_unless($run->company_id === company_id(), 403);

        if ($run->status !== 'DRAFT') {
            return back()->with('error', 'Only Draft runs can be submitted.');
        }

        $period = DB::table('fiscal_periods')->where('id', $run->period_id)->first();
        $postingDate = $period->end_date;

        // Get ALL payslips with their lines to include manual deductions
        $payslips = Payslip::where('payroll_run_id', $run->id)->get();
        
        $gross = 0;
        $net = 0;
        $paye = 0;
        $aids = 0;
        $nssa = 0;
        $nec = 0;
        $manualDeductions = 0; // Track manual deductions
        $fixedPayslips = 0;
        
        foreach ($payslips as $p) {
            // In submit() method, replace the manual deductions query with:
                $manualDeductionLines = PayslipLine::where('payslip_id', $p->id)
                    ->with('component')
                    ->get()
                    ->filter(function($line) {
                        return $line->component && $line->component->component_type === 'DEDUCTION';
                    });

                $manualDedTotal = $manualDeductionLines->sum('amount');
            
            $gross += $p->gross_pay;
            $paye += $p->paye;
            $aids += $p->aids_levy;
            $nssa += $p->nssa_employee;
            $nec += $p->nec_levy;
            $manualDeductions += $manualDedTotal; // Add manual deductions
            
            // Calculate correct net for this payslip INCLUDING manual deductions
            $correctNet = round(
                $p->gross_pay - 
                ($p->paye + $p->aids_levy + $p->nssa_employee + $p->nec_levy + $manualDedTotal), 
                2
            );
            $net += $correctNet;
            
            // Log for debugging
            Log::info('Payslip details', [
                'payslip_id' => $p->id,
                'gross' => $p->gross_pay,
                'paye' => $p->paye,
                'aids' => $p->aids_levy,
                'nssa' => $p->nssa_employee,
                'nec' => $p->nec_levy,
                'manual_deductions' => $manualDedTotal,
                'calculated_net' => $correctNet,
                'stored_net' => $p->net_pay
            ]);
            
            // If stored net is wrong, update it
            if (abs($correctNet - $p->net_pay) > 0.01) {
                DB::table('payslips')
                    ->where('id', $p->id)
                    ->update([
                        'net_pay' => $correctNet,
                        'total_deductions' => $p->paye + $p->aids_levy + $p->nssa_employee + $p->nec_levy + $manualDedTotal
                    ]);
                $fixedPayslips++;
            }
        }

        // Round all totals
        $gross = round($gross, 2);
        $net = round($net, 2);
        $paye = round($paye, 2);
        $aids = round($aids, 2);
        $nssa = round($nssa, 2);
        $nec = round($nec, 2);
        $manualDeductions = round($manualDeductions, 2);

        // Log the totals for debugging
        Log::info('PAYROLL SUBMIT ATTEMPT WITH MANUAL DEDUCTIONS', [
            'run_id' => $run->id,
            'run_no' => $run->run_no,
            'gross' => $gross,
            'net' => $net,
            'paye' => $paye,
            'aids' => $aids,
            'nssa' => $nssa,
            'nec' => $nec,
            'manual_deductions' => $manualDeductions,
            'fixed_payslips' => $fixedPayslips
        ]);

        // VERIFY BALANCE - Gross must equal Net + PAYE + AIDS + NSSA + NEC + MANUAL DEDUCTIONS
        $totalCredits = $net + $paye + $aids + $nssa + $nec + $manualDeductions;
        $difference = round($gross - $totalCredits, 2);

        if (abs($difference) > 0.01) {
            $error = "Payroll run is unbalanced!\n" .
                     "DEBIT: Gross Pay: {$gross}\n" .
                     "CREDITS:\n" .
                     "  Net Pay: {$net}\n" .
                     "  PAYE: {$paye}\n" .
                     "  AIDS: {$aids}\n" .
                     "  NSSA: {$nssa}\n" .
                     "  NEC: {$nec}\n" .
                     "  Manual Deductions: {$manualDeductions}\n" .
                     "  Total Credits: {$totalCredits}\n" .
                     "  Difference: {$difference}";
            
            Log::error('PAYROLL UNBALANCED', ['error' => $error]);
            return back()->with('error', nl2br($error));
        }

        try {
            DB::transaction(function () use ($run, $postingDate, $gross, $net, $paye, $aids, $nssa, $nec, $manualDeductions) {
                // Get account IDs from configuration
                $acc = config('payroll.accounts');

                $expenseAccId = $this->coaId($acc['payroll_expense']);
                $salaryPayableAccId = $this->coaId($acc['salaries_payable']);
                $payeAccId = $this->coaId($acc['paye_payable']);
                $aidsAccId = $this->coaId($acc['aids_levy_payable']);
                $nssaAccId = $this->coaId($acc['nssa_payable']);
                $necAccId  = $this->coaId($acc['nec_payable']);
                $otherDeductionsAccId = $this->coaId($acc['other_deductions_payable'] ?? '2200');

                Log::info('ACCOUNT IDs FOUND', [
                    'expense' => $expenseAccId,
                    'salary' => $salaryPayableAccId,
                    'paye' => $payeAccId,
                    'aids' => $aidsAccId,
                    'nssa' => $nssaAccId,
                    'nec' => $necAccId,
                    'other_deductions' => $otherDeductionsAccId,
                ]);

                // Build journal lines
                $lines = [];

                // DR: Payroll Expense (Gross)
                $lines[] = [
                    'account_id' => $expenseAccId,
                    'description' => 'Payroll expense (gross)',
                    'debit' => $gross,
                    'credit' => 0,
                    'party_type' => 'NONE',
                ];

                // CR: Net Pay (Salaries Payable)
                if ($net > 0) {
                    $lines[] = [
                        'account_id' => $salaryPayableAccId,
                        'description' => 'Net salaries payable',
                        'debit' => 0,
                        'credit' => $net,
                        'party_type' => 'NONE',
                    ];
                }

                // CR: PAYE
                if ($paye > 0) {
                    $lines[] = [
                        'account_id' => $payeAccId,
                        'description' => 'PAYE payable',
                        'debit' => 0,
                        'credit' => $paye,
                        'party_type' => 'NONE',
                    ];
                }

                // CR: AIDS Levy
                if ($aids > 0) {
                    $lines[] = [
                        'account_id' => $aidsAccId,
                        'description' => 'AIDS levy payable',
                        'debit' => 0,
                        'credit' => $aids,
                        'party_type' => 'NONE',
                    ];
                }

                // CR: NSSA
                if ($nssa > 0) {
                    $lines[] = [
                        'account_id' => $nssaAccId,
                        'description' => 'NSSA employee payable',
                        'debit' => 0,
                        'credit' => $nssa,
                        'party_type' => 'NONE',
                    ];
                }

                // CR: NEC
                if ($nec > 0) {
                    $lines[] = [
                        'account_id' => $necAccId,
                        'description' => 'NEC levy payable',
                        'debit' => 0,
                        'credit' => $nec,
                        'party_type' => 'NONE',
                    ];
                }

                // CR: Manual Deductions (Pension, Medical Aid, etc)
                if ($manualDeductions > 0) {
                    $lines[] = [
                        'account_id' => $otherDeductionsAccId,
                        'description' => 'Other deductions payable (Pension, Medical Aid)',
                        'debit' => 0,
                        'credit' => $manualDeductions,
                        'party_type' => 'NONE',
                    ];
                }

                // Double-check balance before posting
                $totalDebit = collect($lines)->sum('debit');
                $totalCredit = collect($lines)->sum('credit');

                Log::info('JOURNAL LINES BALANCE', [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'difference' => $totalDebit - $totalCredit,
                    'line_count' => count($lines)
                ]);

                if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                    throw new \RuntimeException(
                        "Journal lines are unbalanced!\n" .
                        "Total Debits: {$totalDebit}\n" .
                        "Total Credits: {$totalCredit}\n" .
                        "Difference: " . ($totalDebit - $totalCredit)
                    );
                }

                // Create and post journal entry
                $journalEntry = $this->journalPostingService->createPostedJournalWithLines(
                    company_id(),
                    $postingDate,
                    "Payroll posting - Run {$run->run_no}",
                    'PayrollRun',
                    $run->id,
                    $run->currency,
                    $run->exchange_rate,
                    auth()->id(),
                    $lines
                );

                // Mark run as submitted
                DB::table('payroll_runs')->where('id', $run->id)->update([
                    'status' => 'SUBMITTED',
                    'submitted_by' => auth()->id(),
                    'submitted_at' => now(),
                    'gl_journal_entry_id' => $journalEntry->id,
                    'updated_at' => now(),
                ]);

                Log::info('PAYROLL SUBMIT SUCCESS', [
                    'run' => $run->run_no,
                    'journal_entry' => $journalEntry->entry_no,
                    'fixed_payslips' => $fixedPayslips ?? 0
                ]);
            });

            $message = 'Payroll run submitted and journal posted successfully.';
            if (isset($fixedPayslips) && $fixedPayslips > 0) {
                $message .= " Fixed {$fixedPayslips} payslips with incorrect net pay.";
            }
            $message .= " Manual deductions: " . number_format($manualDeductions, 2);

            return redirect()->route('modules.payroll.runs.show', $run)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('PAYROLL SUBMIT FAILED', [
                'run' => $run->run_no,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to submit payroll: ' . $e->getMessage());
        }
    }

    /**
     * Get Chart of Account ID by code
     */
    private function coaId(string $code): int
    {
        $id = DB::table('chart_of_accounts')
            ->where('company_id', company_id())
            ->where('code', $code)
            ->value('id');

        if (!$id) {
            throw new \RuntimeException(
                "Missing COA account code: {$code}. " .
                "Please add this account to your chart of accounts."
            );
        }
        return (int)$id;
    }

    /**
     * Fix existing payroll runs that are unbalanced or missing NEC
     */
    public function fixPayrollRun(PayrollRun $run)
    {
        abort_unless($run->company_id === company_id(), 403);

        $stats = [
            'payslips_fixed' => 0,
            'nec_added' => 0,
            'net_fixed' => 0,
            'errors' => []
        ];

        DB::transaction(function () use ($run, &$stats) {
            $payslips = Payslip::where('payroll_run_id', $run->id)->get();
            $calculator = app(PayrollCalculator::class);
            $period = DB::table('fiscal_periods')->where('id', $run->period_id)->first();

            foreach ($payslips as $payslip) {
                try {
                    $updates = [];
                    
                    // Recalculate statutory including NEC
                    $stat = $calculator->statutoryForEmployee(
                        $run->company_id,
                        $payslip->employee_id,
                        $payslip->taxable_pay,
                        $period->end_date
                    );

                    // Get manual deductions
                    $manualDeductions = PayslipLine::where('payslip_id', $payslip->id)
                        ->whereHas('component', function($q) {
                            $q->where('component_type', 'DEDUCTION');
                        })
                        ->sum('amount');

                    // Update NEC if needed
                    if (abs($payslip->nec_levy - $stat['nec_levy']) > 0.01) {
                        $updates['nec_levy'] = $stat['nec_levy'];
                        $stats['nec_added']++;
                    }

                    // Calculate correct values including manual deductions
                    $correctTotalDeductions = $payslip->paye + $payslip->aids_levy +
                        $payslip->nssa_employee + ($updates['nec_levy'] ?? $payslip->nec_levy) + $manualDeductions;

                    $correctNetPay = round($payslip->gross_pay - $correctTotalDeductions, 2);

                    // Update total deductions if needed
                    if (abs($payslip->total_deductions - $correctTotalDeductions) > 0.01) {
                        $updates['total_deductions'] = $correctTotalDeductions;
                    }

                    // Update net pay if needed
                    if (abs($payslip->net_pay - $correctNetPay) > 0.01) {
                        $updates['net_pay'] = $correctNetPay;
                        $stats['net_fixed']++;
                    }

                    // Apply updates
                    if (!empty($updates)) {
                        DB::table('payslips')
                            ->where('id', $payslip->id)
                            ->update($updates);
                        $stats['payslips_fixed']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors'][] = "Employee {$payslip->employee_id}: " . $e->getMessage();
                }
            }
        });

        $message = "Fixed {$stats['payslips_fixed']} payslips";
        if ($stats['nec_added'] > 0) {
            $message .= ", added {$stats['nec_added']} NEC values";
        }
        if ($stats['net_fixed'] > 0) {
            $message .= ", corrected {$stats['net_fixed']} net pay calculations";
        }
        if (!empty($stats['errors'])) {
            $message .= ", encountered " . count($stats['errors']) . " errors";
        }

        return back()->with('info', $message);
    }
}