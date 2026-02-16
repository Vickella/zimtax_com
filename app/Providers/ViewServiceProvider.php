<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Nothing needed here for now
    }

    public function boot(): void
    {
        /**
         * Global ERP layout data
         * This guarantees $modules is ALWAYS available in layouts.erp
         */
        View::composer('layouts.erp', function ($view) {

            $modules = [
                [
                    'key'  => 'company-settings',
                    'name' => 'Company Settings',
                    'icon' => '⚙️',
                ],
                [
                    'key'  => 'sales',
                    'name' => 'Sales',
                    'icon' => '🧾',
                ],
                [
                    'key'  => 'purchases',
                    'name' => 'Purchases',
                    'icon' => '🛒',
                ],
                [
                    'key'  => 'inventory',
                    'name' => 'Inventory',
                    'icon' => '📦',
                ],
                [
                    'key'  => 'accounting',
                    'name' => 'Accounting',
                    'icon' => '📒',
                ],
                [
                    'key'  => 'payroll',
                    'name' => 'Payroll',
                    'icon' => '👥',
                ],
                [
                    'key'  => 'tax',
                    'name' => 'Tax',
                    'icon' => '🧮',
                ],
            ];

            $view->with('modules', $modules);
        });
    }
}
