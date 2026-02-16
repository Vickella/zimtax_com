<?php

namespace App\Services\Accounting;

use App\Models\{JournalEntry, JournalLine, GLEntry};
use App\Services\Numbers\NumberSeries;

class JournalPostingService
{
    public function createPostedJournal(
        int $companyId,
        string $postingDate,
        string $memo,
        string $sourceType,
        int $sourceId,
        string $currency,
        float $exchangeRate,
        int $userId
    ): JournalEntry {
        $jeNo = NumberSeries::next('JE', $companyId, 'journal_entries', 'entry_no');

        return JournalEntry::create([
            'company_id' => $companyId,
            'entry_no' => $jeNo,
            'posting_date' => $postingDate,
            'memo' => $memo,
            'status' => 'POSTED',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'currency' => $currency,
            'exchange_rate' => $exchangeRate,
            'created_by' => $userId,
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);
    }

    public function addLine(
        JournalEntry $je,
        int $accountId,
        string $description,
        float $debit,
        float $credit,
        string $partyType = 'NONE',
        ?int $partyId = null
    ): JournalLine {
        $jl = JournalLine::create([
            'journal_entry_id' => $je->id,
            'account_id' => $accountId,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'party_type' => $partyType,
            'party_id' => $partyId,
        ]);

        $rate = (float)($je->exchange_rate ?? 1);
        $amountBase = ($debit - $credit) * $rate;

        GLEntry::create([
            'company_id' => $je->company_id,
            'posting_date' => $je->posting_date,
            'account_id' => $accountId,
            'journal_entry_id' => $je->id,
            'journal_line_id' => $jl->id,
            'debit' => $debit,
            'credit' => $credit,
            'currency' => $je->currency,
            'amount_base' => $amountBase,
            'party_type' => $partyType,
            'party_id' => $partyId,
        ]);

        return $jl;
    }

    public function assertBalanced(JournalEntry $je): void
    {
        $totals = JournalLine::query()
            ->where('journal_entry_id', $je->id)
            ->selectRaw('COALESCE(SUM(debit),0) as dr, COALESCE(SUM(credit),0) as cr')
            ->first();

        $dr = (float)($totals->dr ?? 0);
        $cr = (float)($totals->cr ?? 0);

        if (round($dr, 2) !== round($cr, 2)) {
            throw new \RuntimeException("Journal not balanced for {$je->entry_no}. Dr={$dr}, Cr={$cr}.");
        }
    }
}
