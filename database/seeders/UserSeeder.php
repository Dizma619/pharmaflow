<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // ======================================
        // OWNER
        // ======================================
        User::updateOrCreate(
            ['email' => 'owner@pharmaflow.local'],
            [
                'name' => 'Owner PharmaFlow',
                'phone' => '081234567890',
                'password' => Hash::make('password123'),
                'role' => 'owner',
                'address' => 'Jl. Merdeka No.1',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_active' => true
            ]
        );

        // ======================================
        // STAFF
        // ======================================
        for ($i = 1; $i <= 3; $i++) {

            User::updateOrCreate(
                ['email' => "staff$i@pharmaflow.local"],
                [
                    'name' => "Staff $i",
                    'phone' =>
                    '0812345678'
                        . str_pad(
                            $i,
                            2,
                            '0',
                            STR_PAD_LEFT
                        ),

                    'password' =>
                    Hash::make(
                        'password123'
                    ),

                    'role' => 'staff',

                    'address' =>
                    'Jl. Merdeka No.'
                        . $i,

                    'city' => 'Jakarta',

                    'province' =>
                    'DKI Jakarta',

                    'postal_code' =>
                    '12345',

                    'is_active' =>
                    true
                ]
            );
        }

        // ======================================
        // CUSTOMER
        // ======================================
        User::factory()
            ->count(10)
            ->customer()
            ->create();
    }
}
