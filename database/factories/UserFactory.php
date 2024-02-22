<?php

namespace Database\Factories;

use App\Models\School;
use Database\Factories\Helpers\FactoryHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'is_verified' => false,
            'role' => $this->faker->randomElement(['teacher', 'parent', 'admin']),
            'password' => bcrypt('password'),
            'profile_picture' => $this->faker->imageUrl(),
            'bio' => $this->faker->sentence,
            'contact_information' => $this->faker->phoneNumber,
//            'school_id' => FactoryHelper::getRandomModelId(School::class),
            'school_id' => null,
        ];
    }
}
