<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            WarehouseSeeder::class,
            ShelfSeeder::class,
            MedicineSeeder::class,
            PromotionSeeder::class,
            VoucherSeeder::class,
        ]);
        $this->call([
        TransactionSeeder::class,
    ]);
    }
}