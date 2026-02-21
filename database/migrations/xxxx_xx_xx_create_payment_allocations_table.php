<?php
// database/migrations/xxxx_xx_xx_create_payment_allocations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentAllocationsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->string('invoice_type'); // 'SalesInvoice' or 'PurchaseInvoice'
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();
            
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->index(['invoice_type', 'invoice_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_allocations');
    }
}