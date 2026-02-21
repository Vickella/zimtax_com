<?php
// database/migrations/xxxx_xx_xx_fix_orphaned_journal_entries.php

use Illuminate\Database\Migrations\Migration;
use App\Services\Accounting\JournalPostingService;
use App\Models\JournalEntry;

class FixOrphanedJournalEntries extends Migration
{
    public function up()
    {
        $service = app(JournalPostingService::class);
        
        // Fix entries marked as POSTED but with no GL entries
        $entries = JournalEntry::where('status', 'POSTED')
            ->whereDoesntHave('glEntries')
            ->get();
        
        foreach ($entries as $entry) {
            try {
                DB::transaction(function () use ($entry, $service) {
                    // Check if it has lines
                    if ($entry->lines->count() === 0) {
                        // Delete empty journal entries
                        $entry->delete();
                        return;
                    }
                    
                    // Verify balance
                    $service->assertBalanced($entry);
                    
                    // Create GL entries
                    foreach ($entry->lines as $line) {
                        $rate = (float)($entry->exchange_rate ?? 1);
                        $amountBase = ($line->debit - $line->credit) * $rate;
                        
                        GLEntry::create([
                            'company_id' => $entry->company_id,
                            'posting_date' => $entry->posting_date,
                            'account_id' => $line->account_id,
                            'journal_entry_id' => $entry->id,
                            'journal_line_id' => $line->id,
                            'debit' => $line->debit,
                            'credit' => $line->credit,
                            'currency' => $entry->currency,
                            'amount_base' => $amountBase,
                            'party_type' => $line->party_type,
                            'party_id' => $line->party_id,
                        ]);
                    }
                });
            } catch (\Exception $e) {
                Log::error("Failed to fix entry {$entry->id}: " . $e->getMessage());
            }
        }
    }
    
    public function down()
    {
        // Can't reverse this easily
    }
}