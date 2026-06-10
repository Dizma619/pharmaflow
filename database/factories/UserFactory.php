<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define default state
     */
    public function definition(): array
    {
        return [

            'name' =>
                fake()->name(),

            'email' =>
                fake()->unique()->safeEmail(),

            'phone' =>
                '08' .
                fake()->numerify(
                    '##########'
                ),

            'password' =>
                Hash::make(
                    'password123'
                ),

            'role' =>
                fake()->randomElement([
                    'customer',
                    'staff'
                ]),

            'address' =>
                fake()->streetAddress(),

            'city' =>
                fake()->city(),

            'province' =>
                fake()->state(),

            'postal_code' =>
                fake()->postcode(),

            'latitude' =>
                fake()->latitude(
                    -6.5,
                    -6.0
                ),

            'longitude' =>
                fake()->longitude(
                    106.5,
                    107.0
                ),

            'email_verified_at' =>
                now(),

            'is_active' =>
                true,
        ];
    }

    /**
     * Owner state
     */
    public function owner(): static
    {
        return $this->state(fn () => [
            'role' => 'owner'
        ]);
    }

    /**
     * Staff state
     */
    public function staff(): static
    {
        return $this->state(fn () => [
            'role' => 'staff'
        ]);
    }

    /**
     * Customer state
     */
    public function customer(): static
    {
        return $this->state(fn () => [
            'role' => 'customer'
        ]);
    }
}