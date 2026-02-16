<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class GLEntry extends Model
{
    use BelongsToCompany;

    protected $table = 'gl_entries';
    public $timestamps = false;

    protected $fillable = [
        'company_id','posting_date','account_id',
        'journal_entry_id','journal_line_id',
        'debit','credit','currency','amount_base',
        'party_type','party_id',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'amount_base' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
