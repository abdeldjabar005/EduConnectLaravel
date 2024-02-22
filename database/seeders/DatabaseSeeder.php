<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            SchoolSeeder::class,
            UserSchoolSeeder::class,
            SchoolClassSeeder::class,
            StudentSeeder::class,
            ClassStudentSeeder::class,
            ParentStudentSeeder::class,
        ]);
    }
}
