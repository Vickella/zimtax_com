<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GLEntry extends Model
{
    protected $table = 'gl_entries';
    
    protected $fillable = [
        'company_id', 'posting_date', 'account_id', 'journal_entry_id',
        'journal_line_id', 'debit', 'credit', 'currency', 'amount_base',
        'party_type', 'party_id', 'created_by', 'created_at'
    ];

    protected $casts = [
        'posting_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'amount_base' => 'decimal:2',
    ];

    /**
     * Get the journal entry that owns this GL entry
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Get the journal line that owns this GL entry
     */
    public function journalLine()
    {
        return $this->belongsTo(JournalLine::class, 'journal_line_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}