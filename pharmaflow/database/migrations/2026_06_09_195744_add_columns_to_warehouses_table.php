<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Kita tambah kolom yang dibutuhkan frontend Vue
            if (!Schema::hasColumn('warehouses', 'city')) {
                $table->string('city')->after('name')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'province')) {
                $table->string('province')->after('city')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'address')) {
                $table->text('address')->after('province')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'capacity')) {
                $table->integer('capacity')->after('address')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'status')) {
                $table->string('status')->after('capacity')->default('aktif');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['city', 'province', 'address', 'capacity', 'status']);
        });
    }
};