<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    protected $table = 'sales_invoices';

    protected $fillable = [
        'company_id','invoice_no','invoice_type','customer_id','posting_date','due_date',
        'currency','exchange_rate','status','fiscal_device_serial','fiscal_invoice_number',
        'qr_payload','customer_tin','customer_vat_number','vat_category',
        'subtotal','vat_amount','total','remarks','created_by','submitted_by','submitted_at'
    ];

    protected $casts = [
        'posting_date' => 'date',
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
    ];

    public function lines()
    {
        return $this->hasMany(SalesInvoiceLine::class, 'sales_invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
