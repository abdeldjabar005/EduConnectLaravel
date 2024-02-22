<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Student;
use Database\Seeders\Traits\DisableForeignKeys;
use Database\Seeders\Traits\TruncateTable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassStudentSeeder extends Seeder
{
    use TruncateTable, DisableForeignKeys;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        $this->disableForeignKeys();
//        $this->truncate('class_student');

        $students = Student::all();
        $classes = SchoolClass::all();

        foreach ($students as $student) {
            $student->classes()->attach(
                $classes->random(rand(1, 3))->pluck('id')->toArray()
            );
        }

//        $this->enableForeignKeys();
    }

}
