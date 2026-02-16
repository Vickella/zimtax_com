<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(
        string $auditableType,
        int $auditableId,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $companyId = null
    ): void {
        $resolvedCompanyId = $companyId
            ?? (Auth::user()->company_id ?? null)
            ?? (int) (request()->attributes->get('company')?->id ?? 1);

        AuditLog::create([
            'company_id' => $resolvedCompanyId,
            'user_id' => Auth::id(),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
