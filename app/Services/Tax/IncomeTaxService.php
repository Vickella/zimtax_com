<?php

namespace App\Services\Tax;

use App\Models\Tax\IncomeTaxReturn;
use App\Models\Tax\TaxSetting;
use App\Services\Tax\GlTaxRepository;

class IncomeTaxService
{
    public function __construct(private readonly GlTaxRepository $gl) {}

    public function buildAndSave(int $companyId, TaxSetting $settings, array $data): IncomeTaxReturn
    {
        $start = $data['period_start'];
        $end = $data['period_end'];
        
        // Extract tax year from period_start (IMPORTANT: This was missing)
        $taxYear = $data['tax_year'] ?? date('Y', strtotime($start));

        $rate = (float)($settings->income_tax_rate ?? 0.2575);

        $pl = $this->gl->profitLossTotals($companyId, $start, $end);
        $profitBeforeTax = (float)($pl['income'] ?? 0) - (float)($pl['expense'] ?? 0);

        $addBacks = (float)($data['add_backs'] ?? 0);
        $deductions = (float)($data['deductions'] ?? 0);

        $taxableIncome = $profitBeforeTax + $addBacks - $deductions;

        if (isset($data['override_taxable_income'])) {
            $taxableIncome = (float)$data['override_taxable_income'];
        }

        $taxPayable = $taxableIncome * $rate;

        // Get QPD paid total if applicable
        $qpdPaidTotal = $data['qpd_paid_total'] ?? 0;
        
        // Calculate balance due
        $balanceDue = max(0, $taxPayable - $qpdPaidTotal);

        // Prepare source snapshot
        $sourceSnapshot = [
            'pl' => $pl,
            'input_data' => [
                'add_backs' => $addBacks,
                'deductions' => $deductions,
                'override_taxable_income' => $data['override_taxable_income'] ?? null,
            ]
        ];

        return IncomeTaxReturn::create([
            'company_id' => $companyId,
            'tax_year' => $taxYear, // THIS FIELD WAS MISSING - now included
            'period_start' => $start,
            'period_end' => $end,
            'income_tax_rate' => $rate,
            'profit_before_tax' => round($profitBeforeTax, 2),
            'add_backs' => round($addBacks, 2),
            'deductions' => round($deductions, 2),
            'accounting_profit' => round($profitBeforeTax, 2),
            'non_deductible_expenses' => 0,
            'capital_allowances' => 0,
            'assessed_losses_before' => 0,
            'other_adjustments' => 0,
            'taxable_income' => round($taxableIncome, 2),
            'income_tax_payable' => round($taxPayable, 2),
            'qpd_paid_total' => round($qpdPaidTotal, 2),
            'balance_due' => round($balanceDue, 2),
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'DRAFT',
            'source_snapshot' => $sourceSnapshot,
        ]);
    }
}