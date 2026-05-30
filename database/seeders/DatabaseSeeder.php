<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ClassSeeder::class,
            SubjectSeeder::class,
            StudentSeeder::class,
            TeacherSeeder::class,
            AttendanceSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
