<?php

use App\Models\Company;

if (! function_exists('company_id')) {
    function company_id(): int
    {
        // 1) from request attribute (normal)
        try {
            $reqCompany = request()?->attributes?->get('company');
            if ($reqCompany && isset($reqCompany->id)) {
                return (int) $reqCompany->id;
            }
        } catch (\Throwable $e) {
            // request() may not exist in CLI contexts
        }

        // 2) from app container
        if (app()->bound('currentCompany')) {
            $bound = app('currentCompany');
            if ($bound && isset($bound->id)) {
                // also sync into request if possible
                try { request()?->attributes?->set('company', $bound); } catch (\Throwable $e) {}
                return (int) $bound->id;
            }
        }

        // 3) DB fallback (single-company safe)
        $company = Company::query()
            ->where('is_active', 1)
            ->orderBy('id')
            ->first();

        if ($company) {
            app()->instance('currentCompany', $company);
            try { request()?->attributes?->set('company', $company); } catch (\Throwable $e) {}
            return (int) $company->id;
        }

        // no active company exists
        return 0;
    }
}
