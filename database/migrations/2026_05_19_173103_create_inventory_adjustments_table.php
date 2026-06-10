<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('quantity_before');
            $table->integer('quantity_adjustment');
            $table->integer('quantity_after');
            $table->enum('type', ['penambahan', 'pengurangan', 'koreksi'])->default('koreksi');
            $table->string('reason');
            $table->unsignedBigInteger('adjusted_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('adjusted_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_adjustments');
    }
}