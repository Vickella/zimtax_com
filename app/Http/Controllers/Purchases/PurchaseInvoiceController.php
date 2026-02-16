<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchases\StorePurchaseInvoiceRequest;
use App\Models\Item;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\Purchases\PurchaseInvoiceService;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    public function __construct(private PurchaseInvoiceService $service) {}

    public function index()
    {
        $invoices = PurchaseInvoice::query()
            ->where('company_id', company_id())
            ->with('supplier')
            ->orderByDesc('posting_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('modules.purchases.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $companyId = company_id();

        $invoice = new PurchaseInvoice([
            'posting_date' => now()->toDateString(),
            'currency' => company_currency(),
            'exchange_rate' => 1,
            'status' => 'DRAFT',
        ]);

        $suppliers = Supplier::query()
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $items = Item::query()
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('modules.purchases.invoices.create', compact('invoice','suppliers','items','warehouses'));
    }

    public function store(StorePurchaseInvoiceRequest $request)
    {
        $companyId = company_id();
        $data = $request->validated();

        return DB::transaction(function () use ($companyId, $data) {
            $invoice = $this->service->createDraft($companyId, auth()->id(), $data);
            return redirect()->route('modules.purchases.invoices.show', $invoice)
                ->with('success', 'Purchase invoice saved (Draft).');
        });
    }

    public function show(PurchaseInvoice $invoice)
    {
        abort_unless((int)$invoice->company_id === company_id(), 404);

        $invoice->load(['supplier','lines.item','lines.warehouse']);

        return view('modules.purchases.invoices.show', compact('invoice'));
    }

    public function edit(PurchaseInvoice $invoice)
    {
        abort_unless((int)$invoice->company_id === company_id(), 404);
        abort_if($invoice->status !== 'DRAFT', 403, 'Only DRAFT invoices can be edited.');

        $companyId = company_id();
        $invoice->load('lines');

        $suppliers = Supplier::query()->where('company_id',$companyId)->where('is_active',1)->orderBy('name')->get();
        $items = Item::query()->where('company_id',$companyId)->where('is_active',1)->orderBy('name')->get();
        $warehouses = Warehouse::query()->where('company_id',$companyId)->where('is_active',1)->orderBy('name')->get();

        return view('modules.purchases.invoices.edit', compact('invoice','suppliers','items','warehouses'));
    }

    public function update(StorePurchaseInvoiceRequest $request, PurchaseInvoice $invoice)
    {
        abort_unless((int)$invoice->company_id === company_id(), 404);
        abort_if($invoice->status !== 'DRAFT', 403, 'Only DRAFT invoices can be updated.');

        $data = $request->validated();

        return DB::transaction(function () use ($invoice, $data) {
            $this->service->updateDraft($invoice, $data);
            return redirect()->route('modules.purchases.invoices.show', $invoice)
                ->with('success', 'Purchase invoice updated (Draft).');
        });
    }

    public function submit(PurchaseInvoice $invoice)
    {
        abort_unless((int)$invoice->company_id === company_id(), 404);

        return DB::transaction(function () use ($invoice) {
            $this->service->submit($invoice, auth()->id());
            return redirect()->route('modules.purchases.invoices.show', $invoice)
                ->with('success', 'Purchase invoice submitted.');
        });
    }

    public function cancel(PurchaseInvoice $invoice)
    {
        abort_unless((int)$invoice->company_id === company_id(), 404);

        return DB::transaction(function () use ($invoice) {
            $this->service->cancel($invoice, auth()->id());
            return redirect()->route('modules.purchases.invoices.show', $invoice)
                ->with('success', 'Purchase invoice cancelled.');
        });
    }
}
