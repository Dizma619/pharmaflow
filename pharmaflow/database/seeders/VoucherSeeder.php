<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        $vouchers = [
            [
                'code' => 'WELCOME10',
                'description' => 'Diskon 10% untuk member baru',
                'type' => 'percentage',
                'discount_value' => 10,
                'minimum_purchase' => 50000,
                'max_usage' => 100,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ],
            [
                'code' => 'SEHAT25',
                'description' => 'Diskon Rp25.000 untuk pembelian kesehatan',
                'type' => 'fixed',
                'discount_value' => 25000,
                'minimum_purchase' => 100000,
                'max_usage' => 50,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ],
            [
                'code' => 'FAMILY5',
                'description' => 'Diskon 5% untuk pembelian keluarga',
                'type' => 'percentage',
                'discount_value' => 5,
                'minimum_purchase' => 75000,
                'max_usage' => 200,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ],
        ];

        foreach ($vouchers as $voucher) {

            Voucher::updateOrCreate(

                [
                    'code' => $voucher['code']
                ],

                array_merge(
                    $voucher,
                    [
                        'is_active' => true,
                        'current_usage' => 0
                    ]
                )

            );

        }
    }
}