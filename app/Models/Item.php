<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Item extends Model
{
    protected $table = 'items';
    protected $fillable = [
        'company_id','sku','name','item_type','uom','cost_price','selling_price','vat_category','is_active',
    ];

    protected $casts = [
        'cost_price' => 'float',
        'selling_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function scopeForCompany(Builder $q, int $companyId): Builder
    {
        return $q->where('company_id', $companyId);
    }

    public function scopeActive(Builder $q, ?int $companyId = null): Builder
    {
        $q->where('is_active', 1);
        if ($companyId) $q->where('company_id', $companyId);
        return $q;
    }
}
    