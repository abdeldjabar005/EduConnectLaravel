<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\User;
use Database\Factories\Helpers\FactoryHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teacher = User::find(FactoryHelper::getRandomUserIdByRole('teacher'));

        return [
            'name' => $this->faker->company,
            'grade_level' => $this->faker->numberBetween(1, 12),
            'subject' => $this->faker->randomElement(['Math', 'Science', 'English', 'History', 'Art', 'Music', 'Physical Education']),
            'teacher_id' => $teacher->id,
            'school_id' => $teacher->school_id,
        ];
    }
}
