<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class BankAccount extends Model
{
    use BelongsToCompany;

    protected $table = 'bank_accounts';
    public $timestamps = false;

    protected $fillable = [
        'company_id','name','bank_name','account_number',
        'currency','gl_account_id','is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function glAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'gl_account_id');
    }
}
