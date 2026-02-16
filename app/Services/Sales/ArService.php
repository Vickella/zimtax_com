<?php

namespace App\Services\Sales;

use Illuminate\Support\Facades\DB;

class ArService
{
    public function aging(int $companyId, ?int $customerId = null, ?string $asOf = null): array
    {
        $asOfDate = $asOf ?: now()->toDateString();

        // Outstanding per invoice = invoice.total - sum(allocations)
        $q = DB::table('sales_invoices as si')
            ->selectRaw('
                si.id, si.invoice_no, si.customer_id, si.posting_date, si.due_date, si.currency, si.total,
                COALESCE(SUM(pa.allocated_amount),0) as allocated,
                (si.total - COALESCE(SUM(pa.allocated_amount),0)) as outstanding
            ')
            ->leftJoin('payment_allocations as pa', function ($join) {
                $join->on('pa.reference_id','=','si.id')
                    ->where('pa.reference_type','=','SALES_INVOICE');
            })
            ->where('si.company_id', $companyId)
            ->where('si.status', 'SUBMITTED')
            ->groupBy('si.id');

        if ($customerId) $q->where('si.customer_id', $customerId);

        $rows = $q->get();

        $buckets = [
            'current' => 0,
            '1_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '90_plus' => 0,
        ];

        $detail = [];
        foreach ($rows as $r) {
            if ((float)$r->outstanding <= 0) continue;

            $due = $r->due_date ?: $r->posting_date;
            $days = (int) floor((strtotime($asOfDate) - strtotime($due)) / 86400);

            $bucket = 'current';
            if ($days >= 1 && $days <= 30) $bucket = '1_30';
            elseif ($days >= 31 && $days <= 60) $bucket = '31_60';
            elseif ($days >= 61 && $days <= 90) $bucket = '61_90';
            elseif ($days > 90) $bucket = '90_plus';

            $buckets[$bucket] += (float)$r->outstanding;

            $detail[] = [
                'invoice_id' => $r->id,
                'invoice_no' => $r->invoice_no,
                'customer_id' => $r->customer_id,
                'posting_date' => $r->posting_date,
                'due_date' => $r->due_date,
                'currency' => $r->currency,
                'total' => (float)$r->total,
                'allocated' => (float)$r->allocated,
                'outstanding' => (float)$r->outstanding,
                'days_overdue' => $days,
                'bucket' => $bucket,
            ];
        }

        return ['as_of'=>$asOfDate,'buckets'=>$buckets,'rows'=>$detail];
    }
}
