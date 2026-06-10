<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            [
                'name' => 'PT Kimia Farma',
                'contact_person' => 'Budi Santoso',
                'email' => 'sales@kimiaffarma.co.id',
                'phone' => '0212345678',
                'address' => 'Jl. Inspeksi No.1',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'bank_name' => 'BNI',
                'bank_account' => '1234567890',
                'payment_terms' => '30 hari'
            ],
            [
                'name' => 'PT Kalbe Farma',
                'contact_person' => 'Adi Sutrisno',
                'email' => 'sales@kalbe.co.id',
                'phone' => '0213456789',
                'address' => 'Jl. Tomang Raya No.25',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '11440',
                'bank_name' => 'Mandiri',
                'bank_account' => '0987654321',
                'payment_terms' => '30 hari'
            ],
            [
                'name' => 'PT Dexa Medica',
                'contact_person' => 'Siti Nurhaliza',
                'email' => 'sales@dexa.co.id',
                'phone' => '0214567890',
                'address' => 'Jl. Benda No.1',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12160',
                'bank_name' => 'BCA',
                'bank_account' => '1122334455',
                'payment_terms' => '14 hari'
            ],
        ];

        foreach ($suppliers as $supplier) {

            Supplier::updateOrCreate(

                ['name' => $supplier['name']],

                $supplier

            );

        }
    }
}