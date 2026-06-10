<?php

namespace Database\Seeders;

use App\Models\Shelf;
use Illuminate\Database\Seeder;

class ShelfSeeder extends Seeder
{
    public function run()
    {
        // ===================================
        // SHELF WAREHOUSE 1
        // ===================================
        $shelves = [
            'RAK-A1',
            'RAK-A2',
            'RAK-A3',
            'RAK-B1',
            'RAK-B2',
            'RAK-C1'
        ];

        foreach ($shelves as $shelf) {

            Shelf::updateOrCreate(

                [
                    'warehouse_id' => 1,
                    'code' => $shelf
                ],

                [
                    'name' => $shelf,
                    'description' => "Rak penyimpanan $shelf",
                    'capacity' => 100
                ]
            );
        }

        // ===================================
        // SHELF WAREHOUSE 2
        // ===================================
        $shelves2 = [
            'RAK-D1',
            'RAK-D2',
            'RAK-E1'
        ];

        foreach ($shelves2 as $shelf) {

            Shelf::updateOrCreate(

                [
                    'warehouse_id' => 2,
                    'code' => $shelf
                ],

                [
                    'name' => $shelf,
                    'description' => "Rak penyimpanan $shelf",
                    'capacity' => 100
                ]
            );
        }
    }
}