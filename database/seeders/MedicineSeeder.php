<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run()
    {
        $medicines = [

            ['name' => 'Paracetamol 500mg', 'category_id' => 1, 'supplier_id' => 1, 'base_price' => 2000, 'markup' => 25],
            ['name' => 'Ibuprofen 200mg', 'category_id' => 1, 'supplier_id' => 2, 'base_price' => 3000, 'markup' => 25],
            ['name' => 'Oskadon', 'category_id' => 1, 'supplier_id' => 1, 'base_price' => 5000, 'markup' => 30],

            ['name' => 'Tempra 500mg', 'category_id' => 2, 'supplier_id' => 2, 'base_price' => 4000, 'markup' => 30],
            ['name' => 'Aspirin 500mg', 'category_id' => 2, 'supplier_id' => 3, 'base_price' => 2500, 'markup' => 25],

            ['name' => 'Angin-Angin', 'category_id' => 3, 'supplier_id' => 1, 'base_price' => 3000, 'markup' => 35],
            ['name' => 'Bodrex', 'category_id' => 3, 'supplier_id' => 2, 'base_price' => 4000, 'markup' => 30],

            ['name' => 'Promag', 'category_id' => 4, 'supplier_id' => 1, 'base_price' => 5000, 'markup' => 25],
            ['name' => 'Mylanta', 'category_id' => 4, 'supplier_id' => 3, 'base_price' => 6000, 'markup' => 25],

            ['name' => 'Vitamin C 500mg', 'category_id' => 5, 'supplier_id' => 2, 'base_price' => 2500, 'markup' => 40],
            ['name' => 'Vitamin B Complex', 'category_id' => 5, 'supplier_id' => 1, 'base_price' => 4000, 'markup' => 35],

            ['name' => 'Amoxicillin 500mg', 'category_id' => 6, 'supplier_id' => 3, 'base_price' => 8000, 'markup' => 20],
            ['name' => 'Ciprofloxacin 500mg', 'category_id' => 6, 'supplier_id' => 1, 'base_price' => 10000, 'markup' => 20],

            ['name' => 'Pocin', 'category_id' => 7, 'supplier_id' => 2, 'base_price' => 4000, 'markup' => 30],
            ['name' => 'Omniflam', 'category_id' => 7, 'supplier_id' => 1, 'base_price' => 6000, 'markup' => 25],

            ['name' => 'Loratadine 10mg', 'category_id' => 8, 'supplier_id' => 3, 'base_price' => 5000, 'markup' => 25],
            ['name' => 'Cetirizine 10mg', 'category_id' => 8, 'supplier_id' => 2, 'base_price' => 4000, 'markup' => 30],

        ];

        foreach ($medicines as $index => $medicine) {

            Medicine::updateOrCreate(

                ['name' => $medicine['name']],

                [
                    'code' => 'MED-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'description' => "Deskripsi {$medicine['name']}",
                    'category_id' => $medicine['category_id'],
                    'supplier_id' => $medicine['supplier_id'],
                    'base_price' => $medicine['base_price'],
                    'markup_percentage' => $medicine['markup'],
                    'unit' => 'pcs',
                    'stock_minimum' => 10,
                    'stock_maximum' => 100,
                    'expired_date' => now()->addYear(),
                    'status' => 'aktif'
                ]

            );

        }
    }
}