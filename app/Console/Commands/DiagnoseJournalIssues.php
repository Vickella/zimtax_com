<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\GLEntry;
use App\Services\Accounting\JournalPostingService;

class DiagnoseJournalIssues extends Command
{
    protected $signature = 'journal:diagnose {company?}';
    protected $description = 'Diagnose journal entry issues';

    public function handle(JournalPostingService $service)
    {
        $companyId = $this->argument('company');
        
        $query = JournalEntry::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $entries = $query->get();
        
        $this->info("Total Journal Entries: " . $entries->count());
        
        // Check for status mismatches
        $postedWithGl = 0;
        $postedWithoutGl = 0;
        $draftWithGl = 0;
        $unbalanced = 0;
        
        foreach ($entries as $entry) {
            $hasGl = GLEntry::where('journal_entry_id', $entry->id)->exists();
            
            if ($entry->status === 'POSTED') {
                if ($hasGl) {
                    $postedWithGl++;
                } else {
                    $postedWithoutGl++;
                    $this->warn("POSTED entry {$entry->entry_no} has no GL entries");
                }
            } elseif ($entry->status === 'DRAFT' && $hasGl) {
                $draftWithGl++;
                $this->warn("DRAFT entry {$entry->entry_no} has GL entries!");
            }
            
            // Check balance
            try {
                $service->assertBalanced($entry);
            } catch (\Exception $e) {
                $unbalanced++;
                $this->error("Unbalanced entry: {$entry->entry_no}");
            }
        }
        
        $this->table(['Status', 'Count'], [
            ['POSTED with GL', $postedWithGl],
            ['POSTED without GL', $postedWithoutGl],
            ['DRAFT with GL', $draftWithGl],
            ['Unbalanced', $unbalanced],
        ]);
        
        if ($postedWithoutGl > 0) {
            if ($this->confirm('Fix POSTED entries without GL?')) {
                $stats = $service->fixOrphanedGLEntries($companyId ?? 0);
                $this->info("Fixed: {$stats['fixed']}, Skipped: {$stats['skipped']}, Errors: {$stats['errors']}");
            }
        }
    }
}