<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Employee;
use App\Models\Payroll\PayrollComponent;
use App\Models\Payroll\EmployeePayrollComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::query()
            ->where('company_id', company_id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        return view('modules.payroll.employees.index', compact('employees'));
    }

    public function create()
    {
        [$earningComponents, $deductionComponents] = $this->componentsForForm();

        return view('modules.payroll.employees.create', compact('earningComponents','deductionComponents'));
    }

    public function edit(Employee $employee)
    {
        abort_unless($employee->company_id === company_id(), 403);

        [$earningComponents, $deductionComponents] = $this->componentsForForm();

        $rows = EmployeePayrollComponent::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', 1)
            ->get()
            ->groupBy(fn($r) => $r->component->component_type ?? 'EARNING');

        $earnings = $rows->get('EARNING', collect());
        $deductions = $rows->get('DEDUCTION', collect());

        return view('modules.payroll.employees.edit', compact(
            'employee','earningComponents','deductionComponents','earnings','deductions'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validatedEmployee($request);

        DB::transaction(function () use ($data, $request) {
            $emp = Employee::create($data);

            $this->syncSalaryStructure($emp->id, $request);
        });

        return redirect()->route('modules.payroll.employees.index')->with('success', 'Employee saved.');
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless($employee->company_id === company_id(), 403);

        $data = $this->validatedEmployee($request);

        DB::transaction(function () use ($employee, $data, $request) {
            $employee->update($data);

            EmployeePayrollComponent::where('employee_id', $employee->id)->delete();
            $this->syncSalaryStructure($employee->id, $request);
        });

        return redirect()->route('modules.payroll.employees.index')->with('success', 'Employee updated.');
    }

    private function validatedEmployee(Request $request): array
    {
        $validated = $request->validate([
            'employee_no' => ['required','string','max:50'],
            'first_name'  => ['required','string','max:120'],
            'last_name'   => ['required','string','max:120'],
            'national_id' => ['nullable','string','max:50'],
            'tax_number'  => ['nullable','string','max:50'], // map to tin
            'nssa_number' => ['nullable','string','max:50'],
            'hire_date'   => ['nullable','date'],
            'bank_name'   => ['nullable','string','max:120'],
            'bank_account_number' => ['nullable','string','max:80'],
            'currency'    => ['nullable','string','size:3'],
            'status'      => ['required','in:ACTIVE,INACTIVE'],
        ]);

        return [
            'company_id'          => company_id(),
            'employee_no'         => $validated['employee_no'],
            'first_name'          => $validated['first_name'],
            'last_name'           => $validated['last_name'],
            'national_id'         => $validated['national_id'] ?? null,
            'tin'                 => $validated['tax_number'] ?? null,
            'nssa_number'         => $validated['nssa_number'] ?? null,
            'hire_date'           => $validated['hire_date'] ?? null,
            'bank_name'           => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'currency'            => $validated['currency'] ?? 'ZIG',
            'status'              => strtoupper($validated['status'] ?? 'ACTIVE'),
        ];
    }

    private function syncSalaryStructure(int $employeeId, Request $request): void
    {
        // Statutory components MUST NOT be stored per employee
        $systemNames = config('payroll.system_components', []);

        $systemIds = PayrollComponent::query()
            ->where('company_id', company_id())
            ->whereIn('name', $systemNames)
            ->pluck('id')
            ->all();

        $earnings = $request->input('earnings', []);
        foreach ($earnings as $row) {
            $cid = (int)($row['component_id'] ?? 0);
            $amt = (float)($row['amount'] ?? 0);
            if (!$cid || $amt <= 0) continue;
            if (in_array($cid, $systemIds, true)) continue;

            EmployeePayrollComponent::create([
                'employee_id' => $employeeId,
                'payroll_component_id' => $cid,
                'amount' => $amt,
                'is_active' => 1,
            ]);
        }

        $deductions = $request->input('deductions', []);
        foreach ($deductions as $row) {
            $cid = (int)($row['component_id'] ?? 0);
            $amt = (float)($row['amount'] ?? 0);
            if (!$cid || $amt <= 0) continue;
            if (in_array($cid, $systemIds, true)) continue;

            EmployeePayrollComponent::create([
                'employee_id' => $employeeId,
                'payroll_component_id' => $cid,
                'amount' => $amt,
                'is_active' => 1,
            ]);
        }
    }

    private function componentsForForm(): array
    {
        $systemNames = config('payroll.system_components', []);

        $earningComponents = PayrollComponent::query()
            ->where('company_id', company_id())
            ->where('component_type', 'EARNING')
            ->orderBy('name')
            ->get();

        $deductionComponents = PayrollComponent::query()
            ->where('company_id', company_id())
            ->where('component_type', 'DEDUCTION')
            ->whereNotIn('name', $systemNames) // hide statutory deductions
            ->orderBy('name')
            ->get();

        return [$earningComponents, $deductionComponents];
    }
}
