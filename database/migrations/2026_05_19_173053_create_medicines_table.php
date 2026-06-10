<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicinesTable extends Migration
{
    public function up()
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('supplier_id');
            $table->decimal('base_price', 15, 2);
            $table->decimal('markup_percentage', 5, 2)->default(20);
            $table->decimal('selling_price', 15, 2);
            $table->string('unit')->default('pcs');
            $table->string('photo')->nullable();
            $table->integer('stock_minimum')->default(10);
            $table->integer('stock_maximum')->default(100);
            $table->date('expired_date')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'expired'])->default('aktif');
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicines');
    }
}