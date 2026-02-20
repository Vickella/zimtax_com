<?php

namespace App\Services\Tax;

use App\Models\Tax\Itf12bProjection;
use App\Models\Tax\TaxSetting;
use Illuminate\Support\Collection;

class QpdService
{
    public function createProjection(int $companyId, TaxSetting $settings, array $data): Itf12bProjection
    {
        $growth = (float)($data['growth_rate'] ?? 0);
        $base = (float)$data['base_taxable_income'];
        $rate = (float)($settings->income_tax_rate ?? 0.2575);

        $estimatedIncome = $base * (1 + $growth);
        $estimatedTax = $estimatedIncome * $rate;

        return Itf12bProjection::create([
            'company_id' => $companyId,
            'tax_year' => (int)$data['tax_year'],
            'base_taxable_income' => round($base, 2),
            'growth_rate' => $growth,
            'estimated_taxable_income' => round($estimatedIncome, 2),
            'income_tax_rate' => $rate,
            'estimated_tax_payable' => round($estimatedTax, 2),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function summary(Itf12bProjection $p, TaxSetting $settings): array
    {
        $tax = (float)$p->estimated_tax_payable;

        // Get quarter percentages from settings
        $pcts = [
            1 => (float)$settings->qpd_q1_percent,
            2 => (float)$settings->qpd_q2_percent,
            3 => (float)$settings->qpd_q3_percent,
            4 => (float)$settings->qpd_q4_percent,
        ];

        // Get due dates from settings
        $dueDates = [
            1 => $settings->qpd_q1_due?->format('Y-m-d'),
            2 => $settings->qpd_q2_due?->format('Y-m-d'),
            3 => $settings->qpd_q3_due?->format('Y-m-d'),
            4 => $settings->qpd_q4_due?->format('Y-m-d'),
        ];

        // Get payments grouped by quarter
        $paymentsByQuarter = $p->payments()
            ->get()
            ->groupBy('quarter_no');

        $quarters = [];
        $cumulative = 0.0;
        $paidRunning = 0.0;

        for ($q = 1; $q <= 4; $q++) {
            // Calculate cumulative percentage
            $cumulative += $pcts[$q];
            
            // Calculate cumulative due for this quarter
            $cumDue = $tax * $cumulative;
            
            // Get payments for this quarter safely
            $quarterPayments = $paymentsByQuarter->get($q, collect());
            $thisQuarterPaid = (float) $quarterPayments->sum('amount');
            
            // Calculate balance after including this quarter's payments
            $balance = max(0, $cumDue - ($paidRunning + $thisQuarterPaid));
            
            // Update paid running total (including this quarter)
            $paidRunning += $thisQuarterPaid;
            
            // Build quarter data
            $quarters[$q] = [
                'quarter' => $q,
                'cumulative_percent' => round($cumulative, 4),
                'percent_for_quarter' => round($pcts[$q], 4),
                'cumulative_due' => round($cumDue, 2),
                'due_for_quarter' => round($tax * $pcts[$q], 2),
                'paid_to_date' => round($paidRunning, 2),
                'this_quarter_paid' => round($thisQuarterPaid, 2),
                'balance_due' => round(max(0, $cumDue - $paidRunning), 2),
                'due_date' => $dueDates[$q] ?? null,
                'payment_count' => $quarterPayments->count(),
                'has_payments' => $quarterPayments->isNotEmpty(),
            ];
        }

        return [
            'projection_id' => $p->id,
            'tax_year' => $p->tax_year,
            'estimated_taxable_income' => (float)$p->estimated_taxable_income,
            'estimated_tax_payable' => (float)$p->estimated_tax_payable,
            'currency' => $p->currency ?? 'USD',
            'total_paid' => round($paidRunning, 2),
            'remaining_balance' => round(max(0, $tax - $paidRunning), 2),
            'quarters' => $quarters,
        ];
    }
}