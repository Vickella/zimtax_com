<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Sales\ArService;

class AccountsReceivableController extends Controller
{
    public function aging(ArService $ar)
    {
        $companyId = company_id();
        $customers = Customer::query()->where('company_id',$companyId)->orderBy('name')->get();

        $customerId = request('customer_id') ? (int)request('customer_id') : null;
        $asOf = request('as_of');

        $data = $ar->aging($companyId, $customerId, $asOf);

        return view('modules.sales.ar.aging', compact('customers','data','customerId'));
    }
}
