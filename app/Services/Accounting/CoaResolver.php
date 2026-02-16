<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;

class CoaResolver
{
    public function require(int $companyId, string $code): ChartOfAccount
    {
        $acc = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('code', (string)$code)
            ->where('is_active', 1)
            ->first();

        if (!$acc) {
            $name = config("erp_required_accounts.required_codes.$code") ?? $code;
            throw new \RuntimeException("Missing required account {$code} ({$name}). Create it in Chart of Accounts and ensure it's active.");
        }

        return $acc;
    }

    public function optional(int $companyId, string $code): ?ChartOfAccount
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('code', (string)$code)
            ->where('is_active', 1)
            ->first();
    }
}
