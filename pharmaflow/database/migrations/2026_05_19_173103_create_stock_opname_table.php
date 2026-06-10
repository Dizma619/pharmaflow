<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOpnameTable extends Migration
{
    public function up()
    {
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('medicine_id');
            $table->integer('system_quantity');
            $table->integer('physical_quantity');
            $table->integer('difference');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_opname');
    }
}