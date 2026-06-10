<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->decimal(
                'delivery_latitude',
                11,
                8
            )->nullable()->change();

            $table->decimal(
                'delivery_longitude',
                11,
                8
            )->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->decimal(
                'delivery_latitude',
                10,
                8
            )->nullable()->change();

            $table->decimal(
                'delivery_longitude',
                10,
                8
            )->nullable()->change();
        });
    }
};