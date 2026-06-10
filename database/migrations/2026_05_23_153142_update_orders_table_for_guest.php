<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==========================
        // Update customer_id
        // ==========================
        Schema::table('orders', function (Blueprint $table) {

            // Drop foreign key lama jika ada
            try {
                $table->dropForeign(['customer_id']);
            } catch (\Exception $e) {
                try {
                    DB::statement(
                        'ALTER TABLE orders DROP FOREIGN KEY orders_customer_id_foreign'
                    );
                } catch (\Exception $e) {
                    //
                }
            }

            // Ubah customer_id menjadi nullable
            $table->unsignedBigInteger('customer_id')
                  ->nullable()
                  ->change();
        });

        // Re-add foreign key
        Schema::table('orders', function (Blueprint $table) {

            try {
                $table->foreign('customer_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            } catch (\Exception $e) {
                //
            }
        });

        // ==========================
        // Tambah kolom baru
        // ==========================
        Schema::table('orders', function (Blueprint $table) {

            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')
                      ->nullable()
                      ->after('customer_id');
            }

            if (!Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone')
                      ->nullable()
                      ->after('customer_name');
            }

            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')
                      ->nullable()
                      ->after('delivery_address');
            }

            if (!Schema::hasColumn('orders', 'shipping_province')) {
                $table->string('shipping_province')
                      ->nullable()
                      ->after('delivery_city');
            }

            if (!Schema::hasColumn('orders', 'shipping_postal_code')) {
                $table->string('shipping_postal_code')
                      ->nullable()
                      ->after('shipping_province');
            }

            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->enum(
                    'payment_method',
                    ['cod', 'midtrans', 'bank_transfer']
                )
                ->default('cod')
                ->after('shipping_method');
            }

            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum(
                    'payment_status',
                    [
                        'pending',
                        'completed',
                        'failed',
                        'expired',
                        'cancelled'
                    ]
                )
                ->default('pending')
                ->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')
                      ->nullable()
                      ->after('delivered_at');
            }

            if (!Schema::hasColumn('orders', 'shipped_at')) {
                $table->dateTime('shipped_at')
                      ->nullable()
                      ->after('tracking_number');
            }

            if (!Schema::hasColumn('orders', 'internal_notes')) {
                $table->text('internal_notes')
                      ->nullable()
                      ->after('notes');
            }
        });

        // ==========================
        // Tambah index
        // ==========================
        Schema::table('orders', function (Blueprint $table) {

            try {
                $table->index('customer_phone');
            } catch (\Exception $e) {}

            try {
                $table->index('order_number');
            } catch (\Exception $e) {}

            try {
                $table->index('customer_id');
            } catch (\Exception $e) {}

            try {
                $table->index('status');
            } catch (\Exception $e) {}

            try {
                $table->index('payment_status');
            } catch (\Exception $e) {}

            try {
                $table->index('created_at');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // Drop index
            try { $table->dropIndex(['customer_phone']); } catch (\Exception $e) {}
            try { $table->dropIndex(['order_number']); } catch (\Exception $e) {}
            try { $table->dropIndex(['customer_id']); } catch (\Exception $e) {}
            try { $table->dropIndex(['status']); } catch (\Exception $e) {}
            try { $table->dropIndex(['payment_status']); } catch (\Exception $e) {}
            try { $table->dropIndex(['created_at']); } catch (\Exception $e) {}

            // Drop foreign key
            try {
                $table->dropForeign(['customer_id']);
            } catch (\Exception $e) {}

            // Drop kolom
            $table->dropColumn([
                'customer_name',
                'customer_phone',
                'shipping_address',
                'shipping_province',
                'shipping_postal_code',
                'payment_method',
                'payment_status',
                'tracking_number',
                'shipped_at',
                'internal_notes',
            ]);
        });

        // Kembalikan customer_id
        Schema::table('orders', function (Blueprint $table) {

            $table->unsignedBigInteger('customer_id')
                  ->nullable(false)
                  ->change();

            $table->foreign('customer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};