<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;

// Company Settings
use App\Http\Controllers\CompanySettings\{
    CompanyProfileController,
    CurrencyController,
    ExchangeRateController,
    FiscalPeriodController,
    TaxRateController,
    PayrollStatutorySettingController,
    PayeBracketController
};

// Sales
use App\Http\Controllers\Sales\{
    SalesModuleController,
    CustomerController,
    SalesInvoiceController,
    AccountsReceivableController,
    PaymentReceiptController
};

// Purchases
use App\Http\Controllers\Purchases\{
    PurchasesModuleController,
    SupplierController,
    PurchaseInvoiceController,
    AccountsPayableController,
    PayablesAllocationController
};

// Inventory
use App\Http\Controllers\Inventory\{
    InventoryModuleController,
    ItemController,
    WarehouseController,
    StockLedgerController
};

// Accounting
use App\Http\Controllers\Accounting\{
    ChartOfAccountController,
    JournalEntryController,
    PaymentController,
    AccountingReportController
};

// Payroll
use App\Http\Controllers\Payroll\PayrollModuleController;
use App\Http\Controllers\Payroll\EmployeeController;
use App\Http\Controllers\Payroll\PayrollRunController;
use App\Http\Controllers\Payroll\PayrollReportController;


// Tax Module
use App\Http\Controllers\Tax\TaxModuleController;
use App\Http\Controllers\Tax\TaxSettingsController;
use App\Http\Controllers\Tax\VatReturnController;
use App\Http\Controllers\Tax\QpdController;
use App\Http\Controllers\Tax\IncomeTaxController;


/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('welcome'));

/*
|--------------------------------------------------------------------------
| Authenticated ERP
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ERP Modules (/m/*)
    |--------------------------------------------------------------------------
    */
    Route::prefix('m')
        ->name('modules.')
        ->middleware(['company'])
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | COMPANY SETTINGS
            |--------------------------------------------------------------------------
            */
            Route::prefix('company-settings')
                ->name('company-settings.')
                ->group(function () {

                    // Company Profile
                    Route::get('company', [CompanyProfileController::class, 'edit'])->name('company.edit');
                    Route::put('company', [CompanyProfileController::class, 'update'])->name('company.update');

                    // Currencies
                    Route::get('currencies', [CurrencyController::class, 'index'])->name('currencies.index');
                    Route::post('currencies', [CurrencyController::class, 'store'])->name('currencies.store');
                    Route::put('currencies/{code}', [CurrencyController::class, 'update'])->name('currencies.update');

                    // Exchange Rates
                    Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
                    Route::post('exchange-rates', [ExchangeRateController::class, 'store'])->name('exchange-rates.store');
                    Route::delete('exchange-rates/{id}', [ExchangeRateController::class, 'destroy'])->name('exchange-rates.destroy');

                    // Fiscal Periods
                    Route::get('fiscal-periods', [FiscalPeriodController::class, 'index'])->name('fiscal-periods.index');
                    Route::post('fiscal-periods', [FiscalPeriodController::class, 'store'])->name('fiscal-periods.store');
                    Route::post('fiscal-periods/{id}/close', [FiscalPeriodController::class, 'close'])->name('fiscal-periods.close');

                    // Tax Rates
                    Route::get('tax-rates', [TaxRateController::class, 'index'])->name('tax-rates.index');
                    Route::post('tax-rates', [TaxRateController::class, 'store'])->name('tax-rates.store');
                    Route::put('tax-rates/{id}', [TaxRateController::class, 'update'])->name('tax-rates.update');

                    // Payroll Statutory
                    Route::get('payroll-statutory', [PayrollStatutorySettingController::class, 'index'])->name('payroll-statutory.index');
                    Route::post('payroll-statutory', [PayrollStatutorySettingController::class, 'store'])->name('payroll-statutory.store');

                    // PAYE Brackets
                    Route::get('paye-brackets', [PayeBracketController::class, 'index'])->name('paye-brackets.index');
                    Route::post('paye-brackets', [PayeBracketController::class, 'store'])->name('paye-brackets.store');
                    Route::delete('paye-brackets/{id}', [PayeBracketController::class, 'destroy'])->name('paye-brackets.destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | ACCOUNTING
            |--------------------------------------------------------------------------
            */
            Route::prefix('accounting')
                ->name('accounting.')
                ->middleware(['coa.ready'])
                ->group(function () {

                    // Accounting home
                    Route::view('/', 'modules.accounting.index')->name('index');

                    // Chart of Accounts
                    Route::get('chart', [ChartOfAccountController::class, 'index'])->name('chart.index');
                    Route::get('chart/create', [ChartOfAccountController::class, 'create'])->name('chart.create');
                    Route::post('chart', [ChartOfAccountController::class, 'store'])->name('chart.store');
                    Route::get('chart/{account}/edit', [ChartOfAccountController::class, 'edit'])->name('chart.edit');
                    Route::put('chart/{account}', [ChartOfAccountController::class, 'update'])->name('chart.update');

                    // Journals
                    Route::get('journals', [JournalEntryController::class, 'index'])->name('journals.index');
                    Route::get('journals/create', [JournalEntryController::class, 'create'])->name('journals.create');
                    Route::post('journals', [JournalEntryController::class, 'store'])->name('journals.store');
                    Route::get('journals/{journal}', [JournalEntryController::class, 'show'])->name('journals.show');
                    Route::get('journals/{journal}/edit', [JournalEntryController::class, 'edit'])->name('journals.edit');
                    Route::put('journals/{journal}', [JournalEntryController::class, 'update'])->name('journals.update');
                    Route::post('journals/{journal}/post', [JournalEntryController::class, 'post'])->name('journals.post');
                    Route::post('journals/{journal}/reverse', [JournalEntryController::class, 'reverse'])->name('journals.reverse');
                    Route::post('journals/{journal}/cancel', [JournalEntryController::class, 'cancel'])->name('journals.cancel');

                    // Payments
                    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
                    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
                    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
                    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
                    Route::post('payments/{payment}/submit', [PaymentController::class, 'submit'])->name('payments.submit');
                    Route::post('payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
                    Route::post('payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

                    // Reports (HTML)
                    Route::get('reports/trial-balance', [AccountingReportController::class, 'trialBalance'])->name('reports.trial-balance');
                    Route::get('reports/general-ledger', [AccountingReportController::class, 'generalLedger'])->name('reports.general-ledger');
                    Route::get('reports/profit-loss', [AccountingReportController::class, 'profitLoss'])->name('reports.profit-loss');
                    Route::get('reports/balance-sheet', [AccountingReportController::class, 'balanceSheet'])->name('reports.balance-sheet');

                    // Reports (CSV)
                    Route::get('reports/trial-balance.csv', [AccountingReportController::class, 'trialBalanceCsv'])->name('reports.trial-balance.csv');
                    Route::get('reports/profit-loss.csv', [AccountingReportController::class, 'profitLossCsv'])->name('reports.profit-loss.csv');
                    Route::get('reports/balance-sheet.csv', [AccountingReportController::class, 'balanceSheetCsv'])->name('reports.balance-sheet.csv');
                     Route::get('reports/general-ledger.csv', [AccountingReportController::class, 'generalLedgerCsv'])->name('reports.general-ledger.csv');
                });


         // Payroll
         Route::prefix('payroll')->name('payroll.')->group(function () {
                    Route::get('/', [PayrollModuleController::class, 'index'])->name('index');

                    // Employees
                    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
                    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
                    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
                    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
                    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');

                    // Payroll Runs
                    Route::get('runs', [PayrollRunController::class, 'index'])->name('runs.index');
                    Route::get('runs/create', [PayrollRunController::class, 'create'])->name('runs.create');
                    Route::post('runs', [PayrollRunController::class, 'store'])->name('runs.store'); // processes payroll
                    Route::get('runs/{run}', [PayrollRunController::class, 'show'])->name('runs.show');
                    Route::post('runs/{run}/submit', [PayrollRunController::class, 'submit'])->name('runs.submit');

                    // Reports
                    Route::get('reports', [PayrollReportController::class, 'index'])->name('reports.index');
                    Route::get('reports/{run}/nssa-p4.csv', [PayrollReportController::class, 'nssaP4Csv'])->name('reports.nssa_p4.csv');
                    Route::get('reports/{run}/zimra-itf16.csv', [PayrollReportController::class, 'zimraItf16Csv'])->name('reports.zimra_itf16.csv');
                    });

            /*
            |--------------------------------------------------------------------------
            | INVENTORY
            |--------------------------------------------------------------------------
            */
            Route::prefix('inventory')->name('inventory.')->group(function () {

                Route::get('/', [InventoryModuleController::class, 'index'])->name('index');

                // Items
                Route::get('items', [ItemController::class, 'index'])->name('items.index');
                Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
                Route::post('items', [ItemController::class, 'store'])->name('items.store');
                Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
                Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');

                // Warehouses
                Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
                Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
                Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
                Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
                Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');

                // Stock Ledger
                Route::get('stock-ledger', [StockLedgerController::class, 'index'])->name('stock-ledger.index');
            });

            /*
            |--------------------------------------------------------------------------
            | PURCHASES
            |--------------------------------------------------------------------------
            */
            Route::prefix('purchases')->name('purchases.')->group(function () {

                Route::get('/', [PurchasesModuleController::class, 'index'])->name('index');

                // Suppliers
                Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
                Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
                Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
                Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
                Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
                Route::post('suppliers/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');

                // Purchase Invoices
                Route::get('invoices', [PurchaseInvoiceController::class, 'index'])->name('invoices.index');
                Route::get('invoices/create', [PurchaseInvoiceController::class, 'create'])->name('invoices.create');
                Route::post('invoices', [PurchaseInvoiceController::class, 'store'])->name('invoices.store');
                Route::get('invoices/{invoice}', [PurchaseInvoiceController::class, 'show'])->name('invoices.show');
                Route::get('invoices/{invoice}/edit', [PurchaseInvoiceController::class, 'edit'])->name('invoices.edit');
                Route::put('invoices/{invoice}', [PurchaseInvoiceController::class, 'update'])->name('invoices.update');

                // Workflow
                Route::post('invoices/{invoice}/submit', [PurchaseInvoiceController::class, 'submit'])->name('invoices.submit');
                Route::post('invoices/{invoice}/cancel', [PurchaseInvoiceController::class, 'cancel'])->name('invoices.cancel');

                // AP Aging
                Route::get('ap/aging', [AccountsPayableController::class, 'aging'])->name('ap.aging');
            });


            // Tax Module
            Route::prefix('tax')->name('tax.')->group(function () {

                Route::get('/', [\App\Http\Controllers\Tax\TaxModuleController::class, 'index'])->name('index');

                // Settings
                Route::get('/settings', [\App\Http\Controllers\Tax\TaxSettingsController::class, 'edit'])->name('settings.edit');
                Route::post('/settings', [\App\Http\Controllers\Tax\TaxSettingsController::class, 'update'])->name('settings.update');

                // VAT Return (VAT 7)
                Route::get('/vat', [\App\Http\Controllers\Tax\VatReturnController::class, 'index'])->name('vat.index');
                Route::get('/vat/create', [\App\Http\Controllers\Tax\VatReturnController::class, 'create'])->name('vat.create');
                Route::post('/vat', [\App\Http\Controllers\Tax\VatReturnController::class, 'store'])->name('vat.store');
                Route::get('/vat/{vatReturn}', [\App\Http\Controllers\Tax\VatReturnController::class, 'show'])->name('vat.show');
                Route::get('/vat/{vatReturn}/pdf', [\App\Http\Controllers\Tax\VatReturnController::class, 'pdf'])->name('vat.pdf');
                Route::get('/vat/{vatReturn}/excel', [\App\Http\Controllers\Tax\VatReturnController::class, 'excel'])->name('vat.excel');

                // QPD / ITF12B
                Route::get('/qpd', [\App\Http\Controllers\Tax\QpdController::class, 'index'])->name('qpd.index');
                Route::get('/qpd/create', [\App\Http\Controllers\Tax\QpdController::class, 'create'])->name('qpd.create');
                Route::post('/qpd', [\App\Http\Controllers\Tax\QpdController::class, 'store'])->name('qpd.store');
                Route::get('/qpd/{projection}', [\App\Http\Controllers\Tax\QpdController::class, 'show'])->name('qpd.show');
                Route::post('/qpd/{projection}/pay/{quarterNo}', [\App\Http\Controllers\Tax\QpdController::class, 'recordPayment'])->name('qpd.pay');
                Route::get('/qpd/{projection}/pdf/{quarterNo}', [\App\Http\Controllers\Tax\QpdController::class, 'pdf'])->name('qpd.pdf');
                Route::get('/qpd/{projection}/excel', [\App\Http\Controllers\Tax\QpdController::class, 'excel'])->name('qpd.excel');

                // Income Tax / ITF12C
                Route::get('/income-tax', [\App\Http\Controllers\Tax\IncomeTaxController::class, 'index'])->name('income.index');
                Route::get('/income-tax/create', [\App\Http\Controllers\Tax\IncomeTaxController::class, 'create'])->name('income.create');
                Route::post('/income-tax', [\App\Http\Controllers\Tax\IncomeTaxController::class, 'store'])->name('income.store');
                Route::get('/income-tax/{incomeTaxReturn}', [\App\Http\Controllers\Tax\IncomeTaxController::class, 'show'])->name('income.show');
                Route::get('/income-tax/{incomeTaxReturn}/pdf', [\App\Http\Controllers\Tax\IncomeTaxController::class, 'pdf'])->name('income.pdf');
                Route::get('/income-tax/{incomeTaxReturn}/excel', [\App\Http\Controllers\Tax\IncomeTaxController::class, 'excel'])->name('income.excel');
            });


            /*
            |--------------------------------------------------------------------------
            | SALES
            |--------------------------------------------------------------------------
            */
            Route::prefix('sales')->name('sales.')->group(function () {

                Route::get('/', [SalesModuleController::class, 'index'])->name('index');

                // Customers
                Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
                Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
                Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
                Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
                Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

                // Sales Invoices
                Route::get('invoices', [SalesInvoiceController::class, 'index'])->name('invoices.index');
                Route::get('invoices/create', [SalesInvoiceController::class, 'create'])->name('invoices.create');
                Route::post('invoices', [SalesInvoiceController::class, 'store'])->name('invoices.store');
                Route::get('invoices/{invoice}', [SalesInvoiceController::class, 'show'])->name('invoices.show');
                Route::get('invoices/{invoice}/edit', [SalesInvoiceController::class, 'edit'])->name('invoices.edit');
                Route::put('invoices/{invoice}', [SalesInvoiceController::class, 'update'])->name('invoices.update');

                Route::post('invoices/{invoice}/submit', [SalesInvoiceController::class, 'submit'])->name('invoices.submit');
                Route::post('invoices/{invoice}/cancel', [SalesInvoiceController::class, 'cancel'])->name('invoices.cancel');

                // AR Aging
                Route::get('ar/aging', [AccountsReceivableController::class, 'aging'])->name('ar.aging');

                // Payment Receipts
                Route::get('receipts', [PaymentReceiptController::class, 'index'])->name('receipts.index');
                Route::get('receipts/create', [PaymentReceiptController::class, 'create'])->name('receipts.create');
                Route::post('receipts', [PaymentReceiptController::class, 'store'])->name('receipts.store');
                Route::get('receipts/{payment}', [PaymentReceiptController::class, 'show'])->name('receipts.show');

                // Allocation (note: kept as you provided)
                Route::get('ap/allocate', [PayablesAllocationController::class, 'create'])->name('ap.allocate');
                Route::get('ap/supplier/{supplierId}/open-invoices', [PayablesAllocationController::class, 'supplierOpenInvoices'])->name('ap.open_invoices');
                Route::post('ap/allocate', [PayablesAllocationController::class, 'store'])->name('ap.allocate.store');
            });

            /*
            |--------------------------------------------------------------------------
            | Generic module section routes (used by sidebar/dashboard)
            |--------------------------------------------------------------------------
            */
            Route::get('{module}/masters', [ModuleController::class, 'section'])
                ->defaults('section', 'masters')
                ->name('masters');

            Route::get('{module}/transactions', [ModuleController::class, 'section'])
                ->defaults('section', 'transactions')
                ->name('transactions');

            Route::get('{module}/reports', [ModuleController::class, 'section'])
                ->defaults('section', 'reports')
                ->name('reports');

            Route::get('{module}/settings', [ModuleController::class, 'section'])
                ->defaults('section', 'settings')
                ->name('settings');

            /*
            |--------------------------------------------------------------------------
            | Catch-all routes (MUST be last)
            |--------------------------------------------------------------------------
            */
            Route::get('{module}', [ModuleController::class, 'index'])->name('index');
            Route::get('{module}/{section}/{page}', [ModuleController::class, 'page'])->name('page');
        });
});

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
