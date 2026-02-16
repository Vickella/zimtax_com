<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'tin',
        'vat_number',
        'bank_details',
        'withholding_tax_flag',
        'is_active',
    ];

    protected $casts = [
        'withholding_tax_flag' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
