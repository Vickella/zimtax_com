<?php

return [
    // Chart of Accounts codes used for payroll journal posting
    'accounts' => [
        'payroll_expense'      => '6100',
        'salaries_payable'     => '2100',

        'paye_payable'         => '2110',
        'aids_levy_payable'    => '2120',
        'nssa_payable'         => '2130',
        'nec_payable'          => '2140',

        'pension_payable'      => '2150',
        'medical_aid_payable'  => '2160',
    ],

    // System component names (not selectable on Employee salary structure)
    'system_components' => [
        'NSSA',
        'PAYE',
        'AIDS Levy',
        'NEC Levy',
    ],
];
