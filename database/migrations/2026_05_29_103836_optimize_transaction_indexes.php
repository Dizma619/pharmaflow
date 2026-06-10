<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'transactions',
            function (
                Blueprint $table
            ) {

                $table->index(
                    'kasir_id'
                );

                $table->index(
                    'payment_status'
                );

                $table->index(
                    'created_at'
                );
            }
        );

        Schema::table(
            'transaction_items',
            function (
                Blueprint $table
            ) {

                $table->index(
                    'transaction_id'
                );

                $table->index(
                    'medicine_id'
                );
            }
        );
    }

    public function down(): void
    {
        //
    }
};
