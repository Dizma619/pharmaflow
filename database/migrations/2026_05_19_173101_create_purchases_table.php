<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up(): void
{
    Schema::create('purchases', function (Blueprint $table) {
        $table->id();
        // INI 2 KOLOM YANG BIKIN ERROR KEMARIN KITA TAMBAHKAN DI SINI:
        $table->string('purchase_number')->unique();
        $table->string('po_number')->nullable(); 
        
        $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
        $table->string('status')->default('pending');
        $table->decimal('total_amount', 15, 2)->default(0);
        $table->text('notes')->nullable();
        $table->integer('items_total')->default(0);
        $table->integer('items_received')->nullable()->default(0);
        $table->timestamp('received_at')->nullable();
        $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamps();
    });
}

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}