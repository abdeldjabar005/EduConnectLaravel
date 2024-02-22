<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParentStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $parents = User::where('role', 'parent')->get();

        foreach ($students as $student) {
            $student->parents()->attach(
                $parents->random(rand(1, 2))->pluck('id')->toArray()
            );
        }
    }
}
