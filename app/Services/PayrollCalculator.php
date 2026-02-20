<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PayrollCalculator
{
    public function statutoryForEmployee(int $companyId, int $employeeId, float $taxablePay, string $postingDate): array
    {
        $settings = $this->getStatutorySettings($companyId, $postingDate);
        $paye = $this->calculatePaye($companyId, $taxablePay, $postingDate);

        $aids = round($paye * (float)$settings['aids_levy_rate'], 2);

        // NSSA uses ceiling base
        $nssaBase = min($taxablePay, (float)$settings['nssa_ceiling_amount']);
        $nssaEmployee = round($nssaBase * (float)$settings['nssa_employee_rate'], 2);
        $nssaEmployer = round($nssaBase * (float)$settings['nssa_employer_rate'], 2);

        // NEC rate from metadata JSONs
        $necRate = (float)($settings['nec_rate']);
        $nec = round($taxablePay * $necRate, 2);

        return [
            'paye'          => $paye,
            'aids_levy'      => $aids,
            'nssa_employee'  => $nssaEmployee,
            'nssa_employer'  => $nssaEmployer,
            'nec_levy'       => $nec,
        ];
    }

    private function getStatutorySettings(int $companyId, string $postingDate): array
    {
        $row = DB::table('payroll_statutory_settings')
            ->where('company_id', $companyId)
            ->where('effective_from', '<=', $postingDate)
            ->where(function ($q) use ($postingDate) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $postingDate);
            })
            ->orderByDesc('effective_from')
            ->first();

        if (!$row) {
            return [
                'nssa_employee_rate' => 0,
                'nssa_employer_rate' => 0,
                'nssa_ceiling_amount'=> 0,
                'aids_levy_rate'     => 0,
                'nec_rate'           => 0,
            ];
        }

        $meta = [];
        if (!empty($row->metadata)) {
            $meta = json_decode($row->metadata, true) ?: [];
        }

        return [
            'nssa_employee_rate' => (float)$row->nssa_employee_rate,
            'nssa_employer_rate' => (float)$row->nssa_employer_rate,
            'nssa_ceiling_amount'=> (float)$row->nssa_ceiling_amount,
            'aids_levy_rate'     => (float)$row->aids_levy_rate,
            'nec_rate'           => (float)$row ->nec_rate,
        ];
    }

    private function calculatePaye(int $companyId, float $taxablePay, string $postingDate): float
    {
        $bands = DB::table('paye_brackets')
            ->where('company_id', $companyId)
            ->where('effective_from', '<=', $postingDate)
            ->where(function ($q) use ($postingDate) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $postingDate);
            })
            ->orderBy('band_order')
            ->get();

        if ($bands->isEmpty()) return 0;

        foreach ($bands as $b) {
            $lower = (float)$b->lower_bound;
            $upper = $b->upper_bound !== null ? (float)$b->upper_bound : null;

            if ($taxablePay >= $lower && ($upper === null || $taxablePay <= $upper)) {
                $rate = (float)$b->rate;
                $baseTax = (float)$b->base_tax;
                $paye = $baseTax + (($taxablePay - $lower) * $rate);
                return round(max(0, $paye), 2);
            }
        }

        return 0;
    }
}
