<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();

            // Transaction info
            $table->dateTime('transaction_date')->index();
            $table->enum('type', ['income', 'expense']); // Pemasukan / Pengeluaran
            $table->string('category'); // penjualan, pembelian, dll
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();

            // Reference (Polymorphic)
            $table->string('reference_type')->nullable(); // 'order', 'transaction', 'purchase'
            $table->unsignedBigInteger('reference_id')->nullable();

            // Additional info
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index(['transaction_date', 'type']);
            $table->index('category');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashflows');
    }
};