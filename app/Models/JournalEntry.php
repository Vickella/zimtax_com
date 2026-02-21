<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $table = 'journal_entries';
    
    protected $fillable = [
        'company_id', 'entry_no', 'posting_date', 'memo', 'status',
        'source_type', 'source_id', 'currency', 'exchange_rate',
        'created_by', 'posted_by', 'posted_at', 'created_at'
    ];

    protected $casts = [
        'posting_date' => 'date',
        'posted_at' => 'datetime',
        'created_at' => 'datetime',
        'exchange_rate' => 'decimal:4',
    ];

    /**
     * Get the lines for this journal entry
     */
    public function lines()
    {
        return $this->hasMany(JournalLine::class, 'journal_entry_id');
    }

    /**
     * Get the GL entries for this journal entry
     * THIS IS THE MISSING RELATIONSHIP
     */
    public function glEntries()
    {
        return $this->hasMany(GLEntry::class, 'journal_entry_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}