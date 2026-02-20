<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollRun;

class PayrollModuleController extends Controller
{
    public function index()
    {
        $runs = PayrollRun::query()
            ->where('company_id', company_id())
            ->orderByDesc('id')
            ->paginate(10); // ✅ now it has links()

        return view('modules.payroll.index');
    }
}
