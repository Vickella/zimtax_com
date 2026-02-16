<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class Warehouse extends Model
{
    use BelongsToCompany;

    protected $table = 'warehouses';

    protected $fillable = [
        'company_id', 'code', 'name', 'location', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
