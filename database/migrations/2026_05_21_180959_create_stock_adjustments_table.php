<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->integer('adjustment_qty');
            $table->enum('type', ['penambahan', 'pengurangan', 'koreksi']); // opname/rusak/hilang
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};