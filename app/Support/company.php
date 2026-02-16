<?php

use App\Models\Company;

if (! function_exists('current_company')) {
    /**
     * Get the currently active company (set by CompanyMiddleware)
     */
    function current_company(): ?Company
    {
        // Preferred: container binding
        if (app()->bound('currentCompany')) {
            return app('currentCompany');
        }

        // Fallback: request attribute
        $company = request()->attributes->get('company');

        return $company instanceof Company ? $company : null;
    }
}

if (! function_exists('company_id')) {
    /**
     * Get current company ID
     */
    function company_id(): int
    {
        $company = current_company();

        if (! $company) {
            abort(403, 'No active company configured.');
        }

        return (int) $company->id;
    }
}

if (! function_exists('company_currency')) {
    /**
     * Get company base currency
     * Column: companies.base_currency
     */
    function company_currency(string $fallback = 'USD'): string
    {
        $company = current_company();

        return $company?->base_currency ?? $fallback;
    }
}
