<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSalesInvoiceRequest;
use App\Models\{SalesInvoice, SalesInvoiceLine, Customer, Item, Warehouse};
use App\Services\Numbers\NumberSeries;
use App\Services\Sales\SalesPostingService;
use App\Services\Sales\SalesTaxCalculator;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
{
    public function __construct(private SalesTaxCalculator $calc) {}

    public function index()
    {
        $invoices = SalesInvoice::query()
            ->where('company_id', company_id())
            ->orderByDesc('posting_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('modules.sales.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $companyId = company_id();

        $customers = Customer::query()
            ->where('company_id',$companyId)
            ->where('is_active',1)
            ->orderBy('name')
            ->get();

        $items = Item::query()
            ->active($companyId)
            ->orderBy('name')
            ->get(['id','sku','name','selling_price','vat_category','uom']);

        $warehouses = Warehouse::query()
            ->where('company_id',$companyId)
            ->where('is_active',1)
            ->orderBy('name')
            ->get();

        return view('modules.sales.invoices.create', compact('customers','items','warehouses'));
    }

    public function store(StoreSalesInvoiceRequest $request)
    {
        $companyId = company_id();
        $data = $request->validated();

        return DB::transaction(function () use ($companyId, $data) {

            $invoiceNo = NumberSeries::next('SI', $companyId, 'sales_invoices', 'invoice_no');

            $customer = Customer::query()
                ->where('company_id',$companyId)
                ->where('id',$data['customer_id'])
                ->firstOrFail();

            // AUTO VAT (server-side)
            $computed = $this->calc->compute($companyId, $data['posting_date'], $data['lines']);

            if (count($computed['lines']) < 1) abort(422, 'At least 1 valid line is required.');

            $invoice = SalesInvoice::create([
                'company_id' => $companyId,
                'invoice_no' => $invoiceNo,
                'invoice_type' => 'TAX_INVOICE',
                'customer_id' => $customer->id,
                'posting_date' => $data['posting_date'],
                'due_date' => $data['due_date'] ?? null,
                'currency' => $data['currency'],
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'status' => 'DRAFT',
                'customer_tin' => $customer->tin,
                'customer_vat_number' => $customer->vat_number,
                'subtotal' => $computed['subtotal'],
                'vat_amount' => $computed['vat_amount'],
                'total' => $computed['total'],
                'remarks' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $invoice->lines()->createMany($computed['lines']);

            return redirect()->route('modules.sales.invoices.show', $invoice)
                ->with('ok','Invoice created (Draft).');
        });
    }

    public function show(SalesInvoice $invoice)
    {
        abort_unless($invoice->company_id === company_id(), 404);
        $invoice->load(['customer','lines.item','lines.warehouse']);
        return view('modules.sales.invoices.show', compact('invoice'));
    }

    public function edit(SalesInvoice $invoice)
    {
        abort_unless($invoice->company_id === company_id(), 404);
        abort_if($invoice->status !== 'DRAFT', 403, 'Only DRAFT invoices can be edited.');

        $companyId = company_id();
        $invoice->load('lines');

        $customers = Customer::query()->where('company_id',$companyId)->where('is_active',1)->orderBy('name')->get();
        $items = Item::query()->active($companyId)->orderBy('name')->get(['id','sku','name','selling_price','vat_category','uom']);
        $warehouses = Warehouse::query()->where('company_id',$companyId)->where('is_active',1)->orderBy('name')->get();

        return view('modules.sales.invoices.edit', compact('invoice','customers','items','warehouses'));
    }

    public function update(StoreSalesInvoiceRequest $request, SalesInvoice $invoice)
    {
        abort_unless($invoice->company_id === company_id(), 404);
        abort_if($invoice->status !== 'DRAFT', 403, 'Only DRAFT invoices can be updated.');

        $companyId = company_id();
        $data = $request->validated();

        return DB::transaction(function () use ($companyId, $data, $invoice) {

            // AUTO VAT (server-side)
            $computed = $this->calc->compute($companyId, $data['posting_date'], $data['lines']);
            if (count($computed['lines']) < 1) abort(422, 'At least 1 valid line is required.');

            $invoice->update([
                'customer_id' => $data['customer_id'],
                'posting_date' => $data['posting_date'],
                'due_date' => $data['due_date'] ?? null,
                'currency' => $data['currency'],
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'subtotal' => $computed['subtotal'],
                'vat_amount' => $computed['vat_amount'],
                'total' => $computed['total'],
                'remarks' => $data['remarks'] ?? null,
            ]);

            SalesInvoiceLine::query()->where('sales_invoice_id',$invoice->id)->delete();
            $invoice->lines()->createMany($computed['lines']);

            return redirect()->route('modules.sales.invoices.show', $invoice)->with('ok','Invoice updated.');
        });
    }

    public function submit(SalesInvoice $invoice, SalesPostingService $service)
    {
        abort_unless($invoice->company_id === company_id(), 404);

        $invoice->load('lines');

        // (Posting service should assume line VAT is correct already, but it can recompute if you want)
        $service->submit($invoice, (int)auth()->id());

        return back()->with('ok','Invoice submitted and posted to GL + Stock Ledger.');
    }

    public function cancel(SalesInvoice $invoice)
    {
        abort_unless($invoice->company_id === company_id(), 404);
        abort_if($invoice->status !== 'DRAFT', 403, 'Only DRAFT invoices can be cancelled.');

        $invoice->update(['status' => 'CANCELLED']);

        return back()->with('ok','Invoice cancelled.');
    }
}
