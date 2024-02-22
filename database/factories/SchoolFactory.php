<?php

namespace Database\Factories;

use Database\Factories\Helpers\FactoryHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'address' => $this->faker->address,
            'image' => $this->faker->imageUrl(),
            'admin_id' => FactoryHelper::getRandomUserIdByRole( 'admin'),
//            'admin_id' => 1,
        ];
    }
}
