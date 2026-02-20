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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollRunController extends Controller
{
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

    // Creates the run AND processes payslips (DRAFT)
    public function store(Request $request, FiscalPeriodService $periodService, PayrollCalculator $calc)
    {
        $data = $request->validate([
            'year'  => ['required','integer','min:2000','max:2100'],
            'month' => ['required','integer','min:1','max:12'],
        ]);

        $companyId = company_id();
        $periodId = $periodService->ensureMonthlyPeriod($companyId, (int)$data['year'], (int)$data['month']);

        $period = DB::table('fiscal_periods')->where('id', $periodId)->first();
        $postingDate = $period->end_date; // typical month end posting

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

            // Component ids for system lines (for payslip_lines storage)
            $systemMap = PayrollComponent::query()
                ->where('company_id', $companyId)
                ->whereIn('name', ['NSSA','PAYE','AIDS Levy','NEC Levy'])
                ->pluck('id','name')
                ->toArray();

            foreach ($employees as $emp) {

                $rows = EmployeePayrollComponent::query()
                    ->where('employee_id', $emp->id)
                    ->where('is_active', 1)
                    ->with('component')
                    ->get();

                $earnings = $rows->filter(fn($r) => $r->component?->component_type === 'EARNING');
                $manualDeductions = $rows->filter(fn($r) => $r->component?->component_type === 'DEDUCTION');

                $gross = round($earnings->sum('amount'), 2);

                // taxable = sum of taxable earnings (your components are taxable=1 currently)
                $taxable = round($earnings->sum(function ($r) {
                    return (int)($r->component?->taxable ?? 1) === 1 ? (float)$r->amount : 0;
                }), 2);

                $stat = $calc->statutoryForEmployee($companyId, $emp->id, $taxable, $postingDate);

                $manualDedTotal = round($manualDeductions->sum('amount'), 2);
                $totalDeductions = round(
                    $manualDedTotal + $stat['paye'] + $stat['aids_levy'] + $stat['nssa_employee'] + $stat['nec_levy'],
                    2
                );

                $net = round($gross - $totalDeductions, 2);

                $payslip = Payslip::create([
                    'payroll_run_id' => $run->id,
                    'employee_id'    => $emp->id,
                    'gross_pay'      => $gross,
                    'taxable_pay'    => $taxable,
                    'paye'           => $stat['paye'],
                    'aids_levy'      => $stat['aids_levy'],
                    'nssa_employee'  => $stat['nssa_employee'],
                    'nssa_employer'  => $stat['nssa_employer'],
                    'zimdef_employee'=> 0,
                    'zimdef_employer'=> 0,
                    'total_deductions'=> $totalDeductions,
                    'net_pay'        => $net,
                ]);

                // Payslip lines: earnings
                foreach ($earnings as $r) {
                    PayslipLine::create([
                        'payslip_id' => $payslip->id,
                        'payroll_component_id' => $r->payroll_component_id,
                        'amount' => round((float)$r->amount, 2),
                    ]);
                }

                // Payslip lines: manual deductions
                foreach ($manualDeductions as $r) {
                    PayslipLine::create([
                        'payslip_id' => $payslip->id,
                        'payroll_component_id' => $r->payroll_component_id,
                        'amount' => round((float)$r->amount, 2),
                    ]);
                }

                // Payslip lines: statutory
                if (!empty($systemMap['PAYE'])) {
                    PayslipLine::create(['payslip_id'=>$payslip->id,'payroll_component_id'=>$systemMap['PAYE'],'amount'=>$stat['paye']]);
                }
                if (!empty($systemMap['AIDS Levy'])) {
                    PayslipLine::create(['payslip_id'=>$payslip->id,'payroll_component_id'=>$systemMap['AIDS Levy'],'amount'=>$stat['aids_levy']]);
                }
                if (!empty($systemMap['NSSA'])) {
                    PayslipLine::create(['payslip_id'=>$payslip->id,'payroll_component_id'=>$systemMap['NSSA'],'amount'=>$stat['nssa_employee']]);
                }
                if (!empty($systemMap['NEC Levy'])) {
                    PayslipLine::create(['payslip_id'=>$payslip->id,'payroll_component_id'=>$systemMap['NEC Levy'],'amount'=>$stat['nec_levy']]);
                }
            }
        });

        return redirect()->route('modules.payroll.runs.show', $run)->with('success', 'Payroll run processed (Draft).');
    }

    public function show(PayrollRun $run)
    {
        abort_unless($run->company_id === company_id(), 403);

        $period = DB::table('fiscal_periods')->where('id', $run->period_id)->first();

        $payslips = Payslip::query()
            ->where('payroll_run_id', $run->id)
            ->with('employee')
            ->orderBy('id')
            ->get();

        return view('modules.payroll.runs.show', compact('run','period','payslips'));
    }

    // Submit = posts Journal + locks run
    public function submit(PayrollRun $run)
    {
        abort_unless($run->company_id === company_id(), 403);
        if ($run->status !== 'DRAFT') {
            return back()->with('error', 'Only Draft runs can be submitted.');
        }

        $period = DB::table('fiscal_periods')->where('id', $run->period_id)->first();
        $postingDate = $period->end_date;

        $totals = Payslip::query()
            ->selectRaw('
                SUM(gross_pay) as gross,
                SUM(net_pay) as net,
                SUM(paye) as paye,
                SUM(aids_levy) as aids,
                SUM(nssa_employee) as nssa_emp,
                SUM(nec_levy) as nec
            ')
            ->where('payroll_run_id', $run->id)
            ->first();

        $gross = round((float)($totals->gross ?? 0), 2);
        $net   = round((float)($totals->net ?? 0), 2);
        $paye  = round((float)($totals->paye ?? 0), 2);
        $aids  = round((float)($totals->aids ?? 0), 2);
        $nssa  = round((float)($totals->nssa_emp ?? 0), 2);
        $nec   = round((float)($totals->nec ?? 0), 2);

        DB::transaction(function () use ($run, $postingDate, $gross, $net, $paye, $aids, $nssa, $nec) {

            // Resolve accounts by code
            $acc = config('payroll.accounts');

            $expenseAccId = $this->coaId($acc['payroll_expense']);
            $salaryPayableAccId = $this->coaId($acc['salaries_payable']);
            $payeAccId = $this->coaId($acc['paye_payable']);
            $aidsAccId = $this->coaId($acc['aids_levy_payable']);
            $nssaAccId = $this->coaId($acc['nssa_payable']);
            $necAccId  = $this->coaId($acc['nec_payable']);

            // Create Journal Entry
            $jeId = DB::table('journal_entries')->insertGetId([
                'company_id' => company_id(),
                'entry_no'   => 'JE-PR-' . $run->id,
                'posting_date' => $postingDate,
                'memo'       => 'Payroll posting - Run ' . $run->run_no,
                'status'     => 'POSTED',
                'source_type'=> 'PayrollRun',
                'source_id'  => $run->id,
                'currency'   => $run->currency,
                'exchange_rate' => $run->exchange_rate,
                'created_by' => auth()->id(),
                'posted_by'  => auth()->id(),
                'posted_at'  => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // DR Payroll Expense (Gross)
            DB::table('journal_lines')->insert([
                'journal_entry_id' => $jeId,
                'account_id' => $expenseAccId,
                'description' => 'Payroll expense (gross)',
                'debit' => $gross,
                'credit' => 0,
                'party_type' => 'NONE',
                'party_id' => null,
                'created_at' => now(),
            ]);

            // CR PAYE
            if ($paye > 0) $this->creditLine($jeId, $payeAccId, 'PAYE payable', $paye);

            // CR AIDS
            if ($aids > 0) $this->creditLine($jeId, $aidsAccId, 'AIDS levy payable', $aids);

            // CR NSSA
            if ($nssa > 0) $this->creditLine($jeId, $nssaAccId, 'NSSA employee payable', $nssa);

            // CR NEC
            if ($nec > 0) $this->creditLine($jeId, $necAccId, 'NEC levy payable', $nec);

            // CR Salaries payable = Net pay
            if ($net > 0) $this->creditLine($jeId, $salaryPayableAccId, 'Net salaries payable', $net);

            // Mark run submitted + store JE reference
            DB::table('payroll_runs')->where('id', $run->id)->update([
                'status' => 'SUBMITTED',
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
                'gl_journal_entry_id' => $jeId,
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Payroll run submitted and journal posted.');
    }

    private function coaId(string $code): int
    {
        $id = DB::table('chart_of_accounts')
            ->where('company_id', company_id())
            ->where('code', $code)
            ->value('id');

        if (!$id) {
            throw new \RuntimeException("Missing COA account code: {$code}. Insert payroll accounts into chart_of_accounts.");
        }
        return (int)$id;
    }

    private function creditLine(int $jeId, int $accId, string $desc, float $amount): void
    {
        DB::table('journal_lines')->insert([
            'journal_entry_id' => $jeId,
            'account_id' => $accId,
            'description' => $desc,
            'debit' => 0,
            'credit' => $amount,
            'party_type' => 'NONE',
            'party_id' => null,
            'created_at' => now(),
        ]);
    }
}
