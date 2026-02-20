<?php

namespace App\Models\Tax;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $table = 'tax_settings';

    protected $fillable = [
        'company_id',
        'vat_rate',
        'income_tax_rate',
        'vat_output_account_code',
        'vat_input_account_code',
        'sales_income_account_codes',
        'purchases_expense_account_codes',
        'qpd_q1_percent','qpd_q2_percent','qpd_q3_percent','qpd_q4_percent',
        'qpd_q1_due','qpd_q2_due','qpd_q3_due','qpd_q4_due',
    ];

    protected $casts = [
        'vat_rate' => 'float',
        'income_tax_rate' => 'float',
        'qpd_q1_percent' => 'float',
        'qpd_q2_percent' => 'float',
        'qpd_q3_percent' => 'float',
        'qpd_q4_percent' => 'float',
        'qpd_q1_due' => 'date',
        'qpd_q2_due' => 'date',
        'qpd_q3_due' => 'date',
        'qpd_q4_due' => 'date',
    ];

    public static function forCompany(int $companyId): self
    {
        return static::firstOrCreate(
            ['company_id' => $companyId],
            [
                'vat_rate' => 0.155,
                'income_tax_rate' => 0.2575,
                'qpd_q1_percent' => 0.10,
                'qpd_q2_percent' => 0.25,
                'qpd_q3_percent' => 0.30,
                'qpd_q4_percent' => 0.35,
                // default placeholders (user edits)
                'qpd_q1_due' => now()->startOfYear()->format('Y-03-25'),
                'qpd_q2_due' => now()->startOfYear()->format('Y-06-25'),
                'qpd_q3_due' => now()->startOfYear()->format('Y-09-25'),
                'qpd_q4_due' => now()->startOfYear()->format('Y-12-20'),
            ]
        );
    }

    public static function upsertCompany(int $companyId, array $data): void
    {
        static::updateOrCreate(['company_id' => $companyId], $data);
    }

    public function salesCodes(): array
    {
        return collect(explode(',', (string)$this->sales_income_account_codes))
            ->map(fn($s) => trim($s))
            ->filter()->values()->all();
    }

    public function purchaseCodes(): array
    {
        return collect(explode(',', (string)$this->purchases_expense_account_codes))
            ->map(fn($s) => trim($s))
            ->filter()->values()->all();
    }
}
