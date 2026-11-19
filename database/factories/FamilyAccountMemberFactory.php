<?php

namespace Database\Factories;

use Cai\Students\Models\FamilyAccountMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyAccountMember>
 */
class FamilyAccountMemberFactory extends Factory
{
    protected $model = FamilyAccountMember::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role' => 'dependiente',
            'valid_from' => now()->toDateString(),
            'valid_until' => null,
        ];
    }
}
