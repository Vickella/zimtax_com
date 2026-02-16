<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchases\StoreSupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::query()
            ->forCompany(company_id())
            ->orderBy('name')
            ->paginate(20);

        return view('modules.purchases.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('modules.purchases.suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        $data = $request->validated();

        $supplier = new Supplier();
        $supplier->fill($data);
        $supplier->company_id = company_id();
        $supplier->withholding_tax_flag = (bool) ($data['withholding_tax_flag'] ?? false);
        $supplier->is_active = (bool) ($data['is_active'] ??  true);
        $supplier->save();

        return redirect()->route('modules.purchases.suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier)
    {
        abort_unless((int)$supplier->company_id === company_id(), 404);
        return view('modules.purchases.suppliers.edit', compact('supplier'));
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier)
    {
        abort_unless((int)$supplier->company_id === company_id(), 404);

        $data = $request->validated();
        $supplier->fill($data);
        $supplier->withholding_tax_flag = (bool) ($data['withholding_tax_flag'] ?? false);
        $supplier->is_active = (bool) ($data['is_active'] ?? true);
        $supplier->save();

        return redirect()->route('modules.purchases.suppliers.index')->with('success', 'Supplier updated.');
    }

        public function restore(int $supplier)
   {
         $companyId = company_id();

         $model = \App\Models\Supplier::withTrashed()
        ->where('company_id', $companyId)
        ->findOrFail($supplier);

         $model->restore();

       return redirect()->route('modules.purchases.suppliers.index')->with('success', 'Supplier restored.');
}

}
