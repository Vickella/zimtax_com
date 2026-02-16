<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $modules = self::modules();

        $shortcuts = [
            ['label' => 'Sales Invoice',     'icon' => '🧾', 'route' => route('modules.transactions', ['module' => 'sales'])],
            ['label' => 'Purchase Invoice',  'icon' => '📥', 'route' => route('modules.transactions', ['module' => 'purchases'])],
            ['label' => 'Cashbook Entry',    'icon' => '🏦', 'route' => route('modules.transactions', ['module' => 'cashbook'])],
            ['label' => 'Items',             'icon' => '📦', 'route' => route('modules.masters', ['module' => 'stock-management'])],
            ['label' => 'Customers',         'icon' => '👤', 'route' => route('modules.masters', ['module' => 'sales'])],
            ['label' => 'Suppliers',         'icon' => '🏭', 'route' => route('modules.masters', ['module' => 'purchases'])],
            ['label' => 'VAT Return',        'icon' => '🧾', 'route' => route('modules.reports', ['module' => 'vat'])],
            ['label' => 'Journal Entry',     'icon' => '📚', 'route' => route('modules.transactions', ['module' => 'accounting'])],
            ['label' => 'Payroll Run',       'icon' => '👥', 'route' => route('modules.transactions', ['module' => 'payroll'])],
            ['label' => 'Compliance Checks', 'icon' => '✅', 'route' => route('modules.reports', ['module' => 'tax-compliance-check'])],
        ];

        $cards = [
            ['title' => 'Today', 'items' => ['Sales Invoices', 'Purchase Invoices', 'Payments', 'Stock Moves']],
            ['title' => 'This Period', 'items' => ['VAT (Draft)', 'Payroll (Draft)', 'QPD (Due)', 'Compliance Flags']],
            ['title' => 'Quick Setup', 'items' => ['Company Profile', 'Tax Rates', 'PAYE Bands', 'Fiscal Periods']],
        ];

        return view('dashboard.home', compact('modules', 'shortcuts', 'cards'));
    }

    public static function modules(): array
    {
        return [
            ['key' => 'company-settings',     'name' => 'Company Settings',     'icon' => '⚙️'],
            ['key' => 'sales',                'name' => 'Sales',                'icon' => '🧾'],
            ['key' => 'purchases',            'name' => 'Purchases',            'icon' => '📥'],
            ['key' => 'cashbook',             'name' => 'Cashbook',             'icon' => '🏦'],
            ['key' => 'vat',                  'name' => 'VAT',                  'icon' => '🧾'],
            ['key' => 'accounting',           'name' => 'Accounting',           'icon' => '📚'],
            ['key' => 'payroll',              'name' => 'Payroll',              'icon' => '👥'],
            ['key' => 'income-tax',           'name' => 'Income Tax',           'icon' => '📈'],
            ['key' => 'tax-compliance-check', 'name' => 'Tax Compliance Check', 'icon' => '✅'],
            ['key' => 'stock-management',     'name' => 'Stock Management',     'icon' => '📦'],
        ];
    }
}
