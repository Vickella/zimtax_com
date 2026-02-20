<?php

namespace App\Models\Tax;

use Illuminate\Database\Eloquent\Model;

class Itf12bProjection extends Model
{
    protected $table = 'itf12b_projections';

    protected $fillable = [
        'company_id','tax_year',
        'base_taxable_income','growth_rate',
        'estimated_taxable_income','income_tax_rate',
        'estimated_tax_payable',
        'notes',
    ];

    protected $casts = [
        'base_taxable_income' => 'float',
        'growth_rate' => 'float',
        'estimated_taxable_income' => 'float',
        'income_tax_rate' => 'float',
        'estimated_tax_payable' => 'float',
    ];

    public function payments()
    {
        return $this->hasMany(Itf12bPayment::class, 'itf12b_projection_id');
    }
}
