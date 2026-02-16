<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class Payment extends Model
{
    use BelongsToCompany;

    protected $table = 'payments';

    protected $fillable = [
        'company_id','payment_no','payment_type','party_type','party_id',
        'bank_account_id','posting_date','currency','exchange_rate',
        'amount','reference','status','created_by','submitted_by','submitted_at',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'exchange_rate' => 'decimal:8',
        'amount' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_id');
    }
}
