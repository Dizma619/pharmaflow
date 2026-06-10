<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (
            Blueprint $table
        ) {

            $table->id();

            $table->string(
                'reference_number'
            )->unique();

            $table->unsignedBigInteger(
                'kasir_id'
            );

            $table->unsignedBigInteger(
                'customer_id'
            )->nullable();

            // MONEY
            $table->decimal(
                'total_amount',
                15,
                2
            );

            $table->decimal(
                'discount_amount',
                15,
                2
            )->default(0);

            $table->decimal(
                'final_amount',
                15,
                2
            );

            // PAYMENT
            $table->enum(
                'payment_method',
                [
                    'cash',
                    'transfer',
                    'credit_card'
                ]
            )->default('cash');

            $table->decimal(
                'cash_received',
                15,
                2
            )->nullable();

            $table->decimal(
                'change_amount',
                15,
                2
            )->nullable();

            $table->enum(
                'payment_status',
                [
                    'lunas',
                    'kredit',
                    'pending'
                ]
            )->default('lunas');

            $table->date(
                'due_date'
            )->nullable();

            $table->text(
                'notes'
            )->nullable();

            $table->timestamps();

            // RELATION
            $table->foreign(
                'kasir_id'
            )
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

            $table->foreign(
                'customer_id'
            )
            ->references('id')
            ->on('users')
            ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists(
            'transactions'
        );
    }
}