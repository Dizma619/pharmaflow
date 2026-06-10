<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'confirmed', 'received', 'invoiced', 'paid'])->default('draft');
            $table->date('purchase_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->integer('items_received')->default(0);
            $table->integer('items_total')->default(0);
            $table->dateTime('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users');
            
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}