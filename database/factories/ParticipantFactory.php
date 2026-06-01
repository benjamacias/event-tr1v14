<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'document_number' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'institution_role' => fake()->jobTitle(),
            'consent_accepted' => true,
        ];
    }
}
