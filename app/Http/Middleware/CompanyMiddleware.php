<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $company = Company::query()
            ->where('is_active', 1)
            ->orderBy('id')
            ->first();

        if (! $company) {
            // no active company at all
            return redirect()
                ->route('modules.company-settings.company.edit')
                ->with('error', 'No active company configured. Please complete Company Settings.');
        }

        // bind for services/models
        app()->instance('currentCompany', $company);

        // request attribute for helper/global scopes
        $request->attributes->set('company', $company);

        // optional: share to views
        view()->share('currentCompany', $company);

        return $next($request);
    }
}
