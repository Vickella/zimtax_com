<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    
    protected $fillable = [
        'company_id',
        'payment_no',
        'payment_type',
        'posting_date',
        'payment_account_id',
        'currency',
        'exchange_rate',
        'amount',
        'reference_no',
        'reference_date',
        'remarks',
        'customer_id',
        'supplier_id',
        'status',
        'created_by',
        'submitted_by',
        'submitted_at',
        'reversed_by',
        'reversed_at',
        'reversal_reason',
        'journal_entry_id',
        'reversal_journal_entry_id',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'reference_date' => 'date',
        'submitted_at' => 'datetime',
        'reversed_at' => 'datetime',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'payment_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function reversalJournalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_journal_entry_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}