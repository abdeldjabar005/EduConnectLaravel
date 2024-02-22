<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Database\Seeders\Traits\TruncateTable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    use TruncateTable, DisableForeignKeys;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        $this->disableForeignKeys();
//        $this->truncate('users');
        Student::factory()->count(10)->create();
//        $this->enableForeignKeys();

    }
}
