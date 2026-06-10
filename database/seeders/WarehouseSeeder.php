<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        $warehouses = [

            [
                'name' => 'Gudang Utama',
                'description' => 'Gudang penyimpanan utama apotek',
                'address' => 'Jl. Merdeka No.1',
            ],

            [
                'name' => 'Gudang Cabang',
                'description' => 'Gudang cabang untuk storage tambahan',
                'address' => 'Jl. Gatot Subroto No.123',
            ],

        ];

        foreach ($warehouses as $warehouse) {

            Warehouse::updateOrCreate(

                ['name' => $warehouse['name']],

                [
                    'description' => $warehouse['description'],
                    'address' => $warehouse['address'],
                ]

            );

        }
    }
}