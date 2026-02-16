<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreCustomerRequest;
use App\Http\Requests\Sales\UpdateCustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::query()
            ->where('company_id', company_id())
            ->orderBy('name')
            ->paginate(20);

        return view('modules.sales.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('modules.sales.customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = company_id();
        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = (int)($request->boolean('is_active', true));

        Customer::create($data);

        return redirect()->route('modules.sales.customers.index')->with('ok','Customer created.');
    }

    public function edit(Customer $customer)
    {
        abort_unless($customer->company_id === company_id(), 404);
        return view('modules.sales.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        abort_unless($customer->company_id === company_id(), 404);

        $data = $request->validated();
        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = (int)($request->boolean('is_active', true));

        $customer->update($data);

        return redirect()->route('sales.customers.index')->with('ok','Customer updated.');
    }
}
