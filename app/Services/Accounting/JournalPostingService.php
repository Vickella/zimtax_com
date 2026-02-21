<?php

namespace App\Services\Accounting;

use App\Models\{JournalEntry, JournalLine, GLEntry};
use App\Services\Numbers\NumberSeries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalPostingService
{
    /**
     * Create a fully posted journal entry with all lines in one transaction
     */
    public function createPostedJournalWithLines(
        int $companyId,
        string $postingDate,
        string $memo,
        string $sourceType,
        int $sourceId,
        string $currency,
        float $exchangeRate,
        int $userId,
        array $lines // Array of line items
    ): JournalEntry {
        return DB::transaction(function () use (
            $companyId, $postingDate, $memo, $sourceType, $sourceId,
            $currency, $exchangeRate, $userId, $lines
        ) {
            // 1. Create journal entry header
            $jeNo = NumberSeries::next('JE', $companyId, 'journal_entries', 'entry_no');
            
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_no' => $jeNo,
                'posting_date' => $postingDate,
                'memo' => $memo,
                'status' => 'DRAFT', // Start as DRAFT, not POSTED
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            // 2. Create all lines
            foreach ($lines as $line) {
                $this->createLine($journalEntry, $line);
            }

            // 3. Verify balance
            $this->assertBalanced($journalEntry);

            // 4. Post the journal entry (creates GL entries)
            $this->postJournalEntry($journalEntry, $userId);

            return $journalEntry->fresh();
        });
    }

    /**
     * Create a single line (doesn't create GL entry yet)
     */
    protected function createLine(JournalEntry $je, array $line): JournalLine
    {
        return JournalLine::create([
            'journal_entry_id' => $je->id,
            'account_id' => $line['account_id'],
            'description' => $line['description'] ?? $je->memo,
            'debit' => $line['debit'] ?? 0,
            'credit' => $line['credit'] ?? 0,
            'party_type' => $line['party_type'] ?? 'NONE',
            'party_id' => $line['party_id'] ?? null,
            'cost_center' => $line['cost_center'] ?? null,
        ]);
    }

    /**
     * Post a journal entry (creates GL entries)
     */
    public function postJournalEntry(JournalEntry $je, int $userId): void
    {
        DB::transaction(function () use ($je, $userId) {
            // Verify it's still in draft
            if ($je->status !== 'DRAFT') {
                throw new \RuntimeException("Journal entry {$je->entry_no} is already posted or voided.");
            }

            // Verify balanced
            $this->assertBalanced($je);

            // Create GL entries for each line
            foreach ($je->lines as $line) {
                $this->createGLEntry($je, $line, $userId);
            }

            // Update journal entry status
            $je->update([
                'status' => 'POSTED',
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            Log::info("Journal entry {$je->entry_no} posted successfully", [
                'company_id' => $je->company_id,
                'source_type' => $je->source_type,
                'source_id' => $je->source_id
            ]);
        });
    }

    /**
     * Create GL entry from a journal line
     */
    protected function createGLEntry(JournalEntry $je, JournalLine $line, int $userId): GLEntry
    {
        $rate = (float)($je->exchange_rate ?? 1);
        $amountBase = ($line->debit - $line->credit) * $rate;

        return GLEntry::create([
            'company_id' => $je->company_id,
            'posting_date' => $je->posting_date,
            'account_id' => $line->account_id,
            'journal_entry_id' => $je->id,
            'journal_line_id' => $line->id,
            'debit' => $line->debit,
            'credit' => $line->credit,
            'currency' => $je->currency,
            'amount_base' => $amountBase,
            'party_type' => $line->party_type,
            'party_id' => $line->party_id,
            'created_by' => $userId,
            'created_at' => now(),
        ]);
    }

    /**
     * Assert that debits equal credits
     */
    public function assertBalanced(JournalEntry $je): void
    {
        // Since JournalLine has $timestamps = false, we need to load relationship
        $je->load('lines');
        
        $dr = round($je->lines->sum('debit'), 2);
        $cr = round($je->lines->sum('credit'), 2);

        if ($dr !== $cr) {
            throw new \RuntimeException(
                "Journal entry {$je->entry_no} is not balanced. " .
                "Total Debits: {$dr}, Total Credits: {$cr}, Difference: " . ($dr - $cr)
            );
        }
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use createPostedJournalWithLines instead
     */
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
        Log::warning('Using deprecated method createPostedJournal', [
            'source_type' => $sourceType,
            'source_id' => $sourceId
        ]);
        
        return JournalEntry::create([
            'company_id' => $companyId,
            'entry_no' => NumberSeries::next('JE', $companyId, 'journal_entries', 'entry_no'),
            'posting_date' => $postingDate,
            'memo' => $memo,
            'status' => 'DRAFT', // Changed from 'POSTED' to 'DRAFT'
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'currency' => $currency,
            'exchange_rate' => $exchangeRate,
            'created_by' => $userId,
            'created_at' => now(),
        ]);
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use createPostedJournalWithLines instead
     */
    public function addLine(
        JournalEntry $je,
        int $accountId,
        string $description,
        float $debit,
        float $credit,
        string $partyType = 'NONE',
        ?int $partyId = null,
        ?string $costCenter = null
    ): JournalLine {
        Log::warning('Using deprecated method addLine', [
            'journal_entry_id' => $je->id,
            'account_id' => $accountId
        ]);
        
        // Just create the line, don't create GL entry yet
        return JournalLine::create([
            'journal_entry_id' => $je->id,
            'account_id' => $accountId,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'party_type' => $partyType,
            'party_id' => $partyId,
            'cost_center' => $costCenter,
        ]);
    }

    /**
     * Fix orphaned GL entries
     */
    public function fixOrphanedGLEntries(int $companyId): array
    {
        $stats = [
            'fixed' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        // Find journal entries marked as POSTED but with no GL entries
        $problematicEntries = JournalEntry::where('company_id', $companyId)
            ->where('status', 'POSTED')
            ->whereDoesntHave('glEntries')
            ->get();

        foreach ($problematicEntries as $je) {
            try {
                DB::transaction(function () use ($je, &$stats) {
                    // Check if it has lines
                    if ($je->lines->count() === 0) {
                        $stats['skipped']++;
                        return;
                    }

                    // Verify balance
                    $this->assertBalanced($je);

                    // Create GL entries for each line
                    foreach ($je->lines as $line) {
                        $rate = (float)($je->exchange_rate ?? 1);
                        $amountBase = ($line->debit - $line->credit) * $rate;
                        
                        GLEntry::create([
                            'company_id' => $je->company_id,
                            'posting_date' => $je->posting_date,
                            'account_id' => $line->account_id,
                            'journal_entry_id' => $je->id,
                            'journal_line_id' => $line->id,
                            'debit' => $line->debit,
                            'credit' => $line->credit,
                            'currency' => $je->currency,
                            'amount_base' => $amountBase,
                            'party_type' => $line->party_type,
                            'party_id' => $line->party_id,
                        ]);
                    }

                    $stats['fixed']++;
                    Log::info("Fixed orphaned journal entry {$je->entry_no}");
                });
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("Failed to fix journal entry {$je->entry_no}: " . $e->getMessage());
            }
        }

        return $stats;
    }

    /**
     * Post all draft journal entries for a specific source
     */
    public function postDraftEntriesForSource(string $sourceType, int $sourceId, int $userId): array
    {
        $results = [
            'total' => 0,
            'posted' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $entries = JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('status', 'DRAFT')
            ->get();

        $results['total'] = $entries->count();

        foreach ($entries as $entry) {
            try {
                $this->postJournalEntry($entry, $userId);
                $results['posted']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'entry_id' => $entry->id,
                    'entry_no' => $entry->entry_no,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}