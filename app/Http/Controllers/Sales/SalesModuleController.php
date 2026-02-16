<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;

class SalesModuleController extends Controller
{
    public function index()
    {
        return view('modules.sales.index', [
            'shortcuts' => [
                [
                    'label' => 'Customers',
                    'icon' => '👥',
                    'route' => route('modules.sales.customers.index'),
                ],
                [
                    'label' => 'Sales Invoices',
                    'icon' => '🧾',
                    'route' => route('modules.sales.invoices.index'),
                ],
                [
                    'label' => 'Receipts',
                    'icon' => '💰',
                    'route' => route('modules.sales.receipts.index'),
                ],
                [
                    'label' => 'AR Aging',
                    'icon' => '📊',
                    'route' => route('modules.sales.ar.aging'),
                ],
            ],
        ]);
    }
}
