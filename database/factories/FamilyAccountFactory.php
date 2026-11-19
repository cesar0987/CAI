<?php

namespace Database\Factories;

use Cai\Students\Models\FamilyAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FamilyAccount>
 */
class FamilyAccountFactory extends Factory
{
    protected $model = FamilyAccount::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_code' => 'FA-'.strtoupper(Str::random(6)),
            'display_name' => 'Familia '.fake()->lastName(),
            'status' => 'active',
            'currency' => 'PYG',
        ];
    }
}
