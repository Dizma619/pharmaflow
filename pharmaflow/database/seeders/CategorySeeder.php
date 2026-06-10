<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Obat Flu', 'description' => 'Obat untuk mengatasi flu dan pilek'],
            ['name' => 'Obat Demam', 'description' => 'Obat penurun demam'],
            ['name' => 'Obat Sakit Kepala', 'description' => 'Obat untuk sakit kepala'],
            ['name' => 'Obat Pencernaan', 'description' => 'Obat untuk masalah pencernaan'],
            ['name' => 'Vitamin', 'description' => 'Suplemen vitamin'],
            ['name' => 'Antibiotik', 'description' => 'Obat antibiotik'],
            ['name' => 'Obat Batuk', 'description' => 'Obat untuk batuk'],
            ['name' => 'Obat Alergi', 'description' => 'Obat untuk alergi'],
        ];

        foreach ($categories as $category) {

            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'slug' => Str::slug($category['name']),
                    'icon' => 'icon-' . Str::slug($category['name']),
                    'is_active' => true
                ]
            );

        }
    }
}