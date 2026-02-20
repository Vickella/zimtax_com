<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollRun;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollReportController extends Controller
{
    public function index()
    {
        $runs = PayrollRun::query()
            ->where('company_id', company_id())
            ->orderByDesc('id')
            ->get();

        return view('modules.payroll.reports.index', compact('runs'));
    }

    public function nssaP4Csv(PayrollRun $run): StreamedResponse
    {
        abort_unless($run->company_id === company_id(), 403);

        $rows = DB::table('payslips as ps')
            ->join('employees as e', 'e.id', '=', 'ps.employee_id')
            ->select([
                'e.employee_no','e.first_name','e.last_name','e.nssa_number',
                'ps.gross_pay','ps.nssa_employee','ps.nssa_employer'
            ])
            ->where('ps.payroll_run_id', $run->id)
            ->orderBy('e.last_name')
            ->get();

        return $this->csv("NSSA_P4_{$run->run_no}.csv", [
            ['Employee No','First Name','Last Name','NSSA No','Gross','NSSA Employee','NSSA Employer'],
            ...$rows->map(fn($r) => [
                $r->employee_no, $r->first_name, $r->last_name, $r->nssa_number,
                $r->gross_pay, $r->nssa_employee, $r->nssa_employer
            ])->toArray()
        ]);
    }

    public function zimraItf16Csv(PayrollRun $run): StreamedResponse
    {
        abort_unless($run->company_id === company_id(), 403);

        $rows = DB::table('payslips as ps')
            ->join('employees as e', 'e.id', '=', 'ps.employee_id')
            ->select([
                'e.employee_no','e.first_name','e.last_name','e.tin',
                'ps.taxable_pay','ps.paye','ps.aids_levy'
            ])
            ->where('ps.payroll_run_id', $run->id)
            ->orderBy('e.last_name')
            ->get();

        return $this->csv("ZIMRA_ITF16_{$run->run_no}.csv", [
            ['Employee No','First Name','Last Name','TIN','Taxable Pay','PAYE','AIDS Levy'],
            ...$rows->map(fn($r) => [
                $r->employee_no, $r->first_name, $r->last_name, $r->tin,
                $r->taxable_pay, $r->paye, $r->aids_levy
            ])->toArray()
        ]);
    }

    private function csv(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($out, $row);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
