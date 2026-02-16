<?php

namespace App\Http\Middleware;

use App\Models\ChartOfAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoaReady
{
    public function handle(Request $request, Closure $next): Response
    {
        // If company is not ready, redirect to company settings (not 403)
        if (company_id() <= 0) {
            return redirect()
                ->route('modules.company-settings.company.edit')
                ->with('error', 'No active company configured. Please complete Company Settings.');
        }

        // Require COA exists
        $hasCoa = ChartOfAccount::query()
            ->where('is_active', 1)
            ->exists();

        if (! $hasCoa) {
            return redirect()
                ->route('modules.accounting.chart.index')
                ->with('error', 'Chart of Accounts not set up yet. Please create/import COA first.');
        }

        return $next($request);
    }
}
