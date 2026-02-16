<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';

    // IMPORTANT: Your schema has NO deleted_at column for chart_of_accounts
    // So DO NOT use SoftDeletes here.

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'account_type',
        'parent_id',
        'is_control_account',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'parent_id' => 'integer',
        'is_control_account' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
