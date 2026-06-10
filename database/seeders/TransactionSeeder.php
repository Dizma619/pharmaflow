<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        // Sesuaikan dengan kolom yang terlihat di phpMyAdmin Anda
        for ($i = 1; $i <= 5; $i++) {
            DB::table('transactions')->insert([
                'reference_number' => 'TRX-' . time() . '-' . $i,
                'kasir_id' => 1,
                'customer_id' => 1, // Wajib diisi karena ada di tabel
                'total_amount' => 100000,
                'discount_amount' => 0,
                'final_amount' => 100000,
                'payment_method' => 'cash',
                'cash_received' => 100000,
                'change_amount' => 0,
                'payment_status' => 'lunas',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}