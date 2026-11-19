<?php

namespace Database\Factories;

use Cai\Students\Models\AuthorizedPickup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuthorizedPickup>
 */
class AuthorizedPickupFactory extends Factory
{
    protected $model = AuthorizedPickup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'document_id' => fake()->unique()->numerify('#######'),
            'phone' => fake()->phoneNumber(),
            'relationship' => 'Abuelo/a',
            'valid_from' => now()->toDateString(),
            'valid_until' => null,
            'is_active' => true,
        ];
    }
}
