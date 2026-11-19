<?php

namespace Database\Factories;

use Cai\Students\Models\Student;
use Cai\Students\Support\StudentCodeGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = 2026;

        return [
            'student_code' => app(StudentCodeGenerator::class)->generate($year),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => fake()->dateTimeBetween('2015-01-01', '2020-12-31')->format('Y-m-d'),
            'status' => 'active',
            'document_id' => fake()->unique()->numerify('#######'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'admission_date' => now()->toDateString(),
        ];
    }
}
