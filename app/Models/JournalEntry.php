<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class JournalEntry extends Model
{
    use BelongsToCompany;

    protected $table = 'journal_entries';

    protected $fillable = [
        'company_id','entry_no','posting_date','memo','status',
        'source_type','source_id','currency','exchange_rate',
        'created_by','posted_by','posted_at',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'exchange_rate' => 'decimal:8',
        'posted_at' => 'datetime',
    ];

    // in JournalEntry model
public function lines()
{
    return $this->hasMany(\App\Models\JournalLine::class, 'journal_entry_id');
}


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
