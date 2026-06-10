<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run()
    {
        $promotions = [
            [
                'name' => 'Diskon Paracetamol 30%',
                'description' => 'Promo spesial untuk obat Paracetamol',
                'type' => 'percentage',
                'discount_value' => 30,
                'medicine_id' => 1,
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'max_quantity' => 50,
            ],
            [
                'name' => 'Gratis Ongkir Bulan Ini',
                'description' => 'Gratis ongkir untuk semua pembelian',
                'type' => 'fixed',
                'discount_value' => 5000,
                'start_date' => now(),
                'end_date' => now()->endOfMonth(),
            ],
        ];

        foreach ($promotions as $promotion) {
            Promotion::create(array_merge($promotion, ['is_active' => true, 'usage_count' => 0]));
        }
    }
}