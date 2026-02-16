<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $table = 'purchase_invoices';

    protected $fillable = [
        'company_id',
        'invoice_no',
        'supplier_id',
        'supplier_invoice_no',
        'supplier_vat_number',
        'supplier_tin',
        'input_tax_document_ref',
        'bill_of_entry_ref',
        'posting_date',
        'due_date',
        'currency',
        'exchange_rate',
        'status',
        'subtotal',
        'vat_amount',
        'total',
        'remarks',
        'created_by',
        'submitted_by',
        'submitted_at',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'due_date' => 'date',
        'exchange_rate' => 'decimal:8',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function lines()
    {
        return $this->hasMany(PurchaseInvoiceLine::class, 'purchase_invoice_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
