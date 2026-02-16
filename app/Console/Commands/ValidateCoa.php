<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Accounting\CoaValidator;

class ValidateCoa extends Command
{
    protected $signature = 'erp:validate-coa {companyId?}';
    protected $description = 'Validate Chart of Accounts readiness for posting';

    public function handle(CoaValidator $validator): int
    {
        $companyId = (int) ($this->argument('companyId') ?? 0);
        if ($companyId <= 0) {
            $companyId = 1;
        }

        $issues = $validator->validate($companyId);

        if (empty($issues)) {
            $this->info("COA OK for company_id={$companyId}");
            return self::SUCCESS;
        }

        $this->error("COA NOT READY for company_id={$companyId}");
        foreach ($issues as $i) $this->line(" - {$i}");

        return self::FAILURE;
    }
}
