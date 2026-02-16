<?php

return [
    'sidebar' => [
        [
            'key' => 'company-settings',
            'label' => 'Company Settings',
            'description' => 'Company profile, users, roles, periods, currencies, statutory defaults.',
            'sections' => [
                'masters' => [
                    ['key' => 'company', 'label' => 'Company Profile'],
                    ['key' => 'users', 'label' => 'Users'],
                    ['key' => 'roles', 'label' => 'Roles & Permissions'],
                    ['key' => 'currencies', 'label' => 'Currencies'],
                    ['key' => 'exchange-rates', 'label' => 'Exchange Rates'],
                    ['key' => 'fiscal-periods', 'label' => 'Fiscal Periods'],
                ],
                'settings' => [
                    ['key' => 'tax-rates', 'label' => 'Tax Rates'],
                    ['key' => 'paye-brackets', 'label' => 'PAYE Brackets'],
                    ['key' => 'payroll-statutory', 'label' => 'Payroll Statutory Settings'],
                ],
                'reports' => [
                    ['key' => 'audit-logs', 'label' => 'Audit Logs'],
                ],
            ],
        ],

        [
            'key' => 'sales',
            'label' => 'Sales',
            'description' => 'Customers, sales invoices, receivables, output VAT sources.',
            'sections' => [
                'masters' => [
                    ['key' => 'customers', 'label' => 'Customers'],
                    ['key' => 'items', 'label' => 'Items'],
                ],
                'transactions' => [
                    ['key' => 'sales-invoice', 'label' => 'Sales Invoice'],
                    ['key' => 'credit-note', 'label' => 'Credit Note'],
                    ['key' => 'debit-note', 'label' => 'Debit Note'],
                ],
                'reports' => [
                    ['key' => 'sales-register', 'label' => 'Sales Register'],
                    ['key' => 'aged-receivables', 'label' => 'Aged Receivables'],
                ],
            ],
        ],

        [
            'key' => 'purchases',
            'label' => 'Purchases',
            'description' => 'Suppliers, purchase invoices, payables, input VAT sources.',
            'sections' => [
                'masters' => [
                    ['key' => 'suppliers', 'label' => 'Suppliers'],
                    ['key' => 'items', 'label' => 'Items'],
                ],
                'transactions' => [
                    ['key' => 'purchase-invoice', 'label' => 'Purchase Invoice'],
                    ['key' => 'bill-of-entry', 'label' => 'Bill of Entry'],
                ],
                'reports' => [
                    ['key' => 'purchase-register', 'label' => 'Purchase Register'],
                    ['key' => 'aged-payables', 'label' => 'Aged Payables'],
                ],
            ],
        ],

        [
            'key' => 'cashbook',
            'label' => 'Cashbook',
            'description' => 'Bank accounts, receipts, payments, allocations and reconciliations.',
            'sections' => [
                'masters' => [
                    ['key' => 'bank-accounts', 'label' => 'Bank Accounts'],
                ],
                'transactions' => [
                    ['key' => 'receipt', 'label' => 'Receipt'],
                    ['key' => 'payment', 'label' => 'Payment'],
                    ['key' => 'allocations', 'label' => 'Payment Allocations'],
                ],
                'reports' => [
                    ['key' => 'cashbook-report', 'label' => 'Cashbook Report'],
                    ['key' => 'bank-reconciliation', 'label' => 'Bank Reconciliation'],
                ],
            ],
        ],

        [
            'key' => 'vat',
            'label' => 'VAT',
            'description' => 'VAT7/VAT7A returns and schedules (invoice-level).',
            'sections' => [
                'transactions' => [
                    ['key' => 'vat7', 'label' => 'VAT7 Return'],
                    ['key' => 'vat7a', 'label' => 'VAT7A Return'],
                ],
                'reports' => [
                    ['key' => 'output-schedule', 'label' => 'Output VAT Schedule'],
                    ['key' => 'input-schedule', 'label' => 'Input VAT Schedule'],
                    ['key' => 'vat-summary', 'label' => 'VAT Summary'],
                ],
            ],
        ],

        [
            'key' => 'accounting',
            'label' => 'Accounting',
            'description' => 'Chart of accounts, journals, GL and financial statements.',
            'sections' => [
                'masters' => [
                    ['key' => 'chart-of-accounts', 'label' => 'Chart of Accounts'],
                ],
                'transactions' => [
                    ['key' => 'journal-entry', 'label' => 'Journal Entry'],
                ],
                'reports' => [
                    ['key' => 'general-ledger', 'label' => 'General Ledger'],
                    ['key' => 'trial-balance', 'label' => 'Trial Balance'],
                    ['key' => 'profit-loss', 'label' => 'Profit & Loss'],
                    ['key' => 'balance-sheet', 'label' => 'Balance Sheet'],
                ],
            ],
        ],

        [
            'key' => 'payroll',
            'label' => 'Payroll',
            'description' => 'Employees, payroll components, runs, payslips and statutory.',
            'sections' => [
                'masters' => [
                    ['key' => 'employees', 'label' => 'Employees'],
                    ['key' => 'payroll-components', 'label' => 'Payroll Components'],
                ],
                'transactions' => [
                    ['key' => 'payroll-run', 'label' => 'Payroll Run'],
                    ['key' => 'payslips', 'label' => 'Payslips'],
                ],
                'reports' => [
                    ['key' => 'paye-schedule', 'label' => 'PAYE Schedule'],
                    ['key' => 'nssa-report', 'label' => 'NSSA Report'],
                ],
            ],
        ],

        [
            'key' => 'income-tax',
            'label' => 'Income Tax',
            'description' => 'Tax years, ITF12B projections and annual tax computations.',
            'sections' => [
                'transactions' => [
                    ['key' => 'tax-year', 'label' => 'Income Tax Year'],
                    ['key' => 'itf12b', 'label' => 'ITF12B Projection'],
                ],
                'reports' => [
                    ['key' => 'income-tax-summary', 'label' => 'Income Tax Summary'],
                ],
            ],
        ],

        [
            'key' => 'tax-compliance-check',
            'label' => 'Tax Compliance Check',
            'description' => 'Flags, findings, rule-based exceptions and auditability.',
            'sections' => [
                'transactions' => [
                    ['key' => 'run-checks', 'label' => 'Run Compliance Checks'],
                ],
                'reports' => [
                    ['key' => 'findings', 'label' => 'Compliance Findings'],
                    ['key' => 'exceptions-dashboard', 'label' => 'Exceptions Dashboard'],
                ],
            ],
        ],

        [
            'key' => 'stock-management',
            'label' => 'Stock Management',
            'description' => 'Warehouses, stock ledger, movements and balances.',
            'sections' => [
                'masters' => [
                    ['key' => 'warehouses', 'label' => 'Warehouses'],
                    ['key' => 'items', 'label' => 'Items'],
                ],
                'transactions' => [
                    ['key' => 'stock-adjustment', 'label' => 'Stock Adjustment'],
                ],
                'reports' => [
                    ['key' => 'stock-ledger', 'label' => 'Stock Ledger'],
                    ['key' => 'stock-balance', 'label' => 'Stock Balance'],
                ],
            ],
        ],
    ],
];
