<?php

namespace App\Services\Numbers;

use Illuminate\Support\Facades\DB;

class NumberSeries
{
    /**
     * Generates next doc number like:
     * SI-202602-000001, JE-202602-000001, PR-202602-000001
     */
    public static function next(string $prefix, int $companyId, string $table, string $column): string
    {
        $yyyymm = now()->format('Ym');
        $base = "{$prefix}-{$yyyymm}-";

        return DB::transaction(function () use ($companyId, $table, $column, $base) {
            // Lock the latest row for this prefix (company scoped)
            $last = DB::table($table)
                ->where('company_id', $companyId)
                ->where($column, 'like', $base.'%')
                ->orderBy($column, 'desc')
                ->lockForUpdate()
                ->value($column);

            $nextSeq = 1;
            if ($last) {
                $seq = (int) substr($last, strlen($base));
                $nextSeq = $seq + 1;
            }

            return $base . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
        });
    }
}
