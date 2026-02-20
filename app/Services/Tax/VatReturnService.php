<?php

namespace App\Services\Tax;

use App\Models\Tax\TaxSetting;
use App\Models\Tax\VatReturn;

class VatReturnService
{
    public function __construct(private readonly GlTaxRepository $gl) {}

    public function buildAndSave(int $companyId, TaxSetting $settings, array $data): VatReturn
    {
        $start = $data['period_start'];
        $end = $data['period_end'];

        $rate = (float)($settings->vat_rate ?? 0.155);

        $outCode = (string)($settings->vat_output_account_code ?? '');
        $inCode = (string)($settings->vat_input_account_code ?? '');

        $outputVat = 0.0;
        $inputVat = 0.0;

        $snapshot = [
            'vat_output_account_code' => $outCode,
            'vat_input_account_code' => $inCode,
            'computed' => [],
        ];

        if ($outCode !== '') {
            $s = $this->gl->sumByAccountCode($companyId, $outCode, $start, $end);
            // VAT output account is liability -> usually CREDIT
            $outputVat = $s['credit'] - $s['debit'];
            $snapshot['computed']['output_account'] = $s;
        }

        if ($inCode !== '') {
            $s = $this->gl->sumByAccountCode($companyId, $inCode, $start, $end);
            // VAT input account is asset -> usually DEBIT
            $inputVat = $s['debit'] - $s['credit'];
            $snapshot['computed']['input_account'] = $s;
        }

        // derive taxable values from VAT / rate (simple default)
        $taxableSales = $rate > 0 ? ($outputVat / $rate) : 0.0;
        $taxablePurchases = $rate > 0 ? ($inputVat / $rate) : 0.0;

        // override support
        if (isset($data['override_output_vat'])) $outputVat = (float)$data['override_output_vat'];
        if (isset($data['override_input_vat'])) $inputVat = (float)$data['override_input_vat'];
        if (isset($data['override_taxable_sales'])) $taxableSales = (float)$data['override_taxable_sales'];
        if (isset($data['override_taxable_purchases'])) $taxablePurchases = (float)$data['override_taxable_purchases'];

        $net = $outputVat - $inputVat;

        return VatReturn::create([
            'company_id' => $companyId,
            'period_start' => $start,
            'period_end' => $end,
            'vat_rate' => $rate,
            'taxable_sales' => round($taxableSales, 2),
            'output_vat' => round($outputVat, 2),
            'taxable_purchases' => round($taxablePurchases, 2),
            'input_vat' => round($inputVat, 2),
            'net_vat_payable' => round($net, 2),
            'notes' => $data['notes'] ?? null,
            'source_snapshot' => $snapshot,
        ]);
    }
}
