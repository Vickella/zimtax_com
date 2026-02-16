<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Currencies
        DB::table('currencies')->updateOrInsert(['code' => 'ZIG'], ['name' => 'Zimbabwe Gold', 'symbol' => 'ZIG', 'is_active' => 1]);
        DB::table('currencies')->updateOrInsert(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'is_active' => 1]);
        DB::table('currencies')->updateOrInsert(['code' => 'ZAR'], ['name' => 'South African Rand', 'symbol' => 'R', 'is_active' => 1]);

        // One company (id will be used by your users.company_id)
        DB::table('companies')->updateOrInsert(
            ['code' => 'MAIN'],
            [
                'name' => 'ZimTax Compliance',
                'trading_name' => null,
                'tin' => null,
                'vat_number' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
                'base_currency' => 'ZIG',
                'fiscal_year_start_month' => 1,
                'is_active' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $companyId = DB::table('companies')->where('code', 'MAIN')->value('id');

        // Tax rates (effective-dated)
        DB::table('tax_rates')->updateOrInsert(
            ['company_id' => $companyId, 'tax_type' => 'VAT', 'code' => 'VAT_STD', 'effective_from' => '2025-01-01'],
            ['description' => 'Standard VAT', 'rate' => 15.5, 'effective_to' => null, 'is_active' => 1, 'metadata' => null, 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('tax_rates')->updateOrInsert(
            ['company_id' => $companyId, 'tax_type' => 'OTHER', 'code' => 'INCOME_TAX', 'effective_from' => '2025-01-01'],
            ['description' => 'Corporate Income Tax', 'rate' => 25.75, 'effective_to' => null, 'is_active' => 1, 'metadata' => null, 'updated_at' => now(), 'created_at' => now()]
        );

        // Payroll statutory settings (NEC stored in metadata)
        DB::table('payroll_statutory_settings')->updateOrInsert(
            ['company_id' => $companyId, 'effective_from' => '2025-01-01'],
            [
                'effective_to' => null,
                'nssa_employee_rate' => 4.5,
                'nssa_employer_rate' => 4.5,
                'nssa_ceiling_amount' => 0,
                'aids_levy_rate' => 3.0,
                'zimdef_employee_rate' => null,
                'zimdef_employer_rate' => null,
                'metadata' => json_encode(['nec_rate' => 1.5]),
                'created_at' => now(),
            ]
        );
    }
}
