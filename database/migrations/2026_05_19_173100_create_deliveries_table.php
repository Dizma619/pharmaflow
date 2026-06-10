<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->decimal('delivery_latitude', 11, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->enum('status', ['pending', 'in_transit', 'delivered', 'failed'])->default('pending');
            $table->timestamp('picked_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
}
