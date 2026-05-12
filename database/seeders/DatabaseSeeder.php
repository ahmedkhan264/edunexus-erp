<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ClassSeeder::class,
            AttendanceSeeder::class,
            StudentSeeder::class,
        ]);

        // Create a test super admin user
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@edunexus.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // Super Admin role
            'is_active' => true,
        ]);

        // Create a test teacher
        User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@edunexus.com',
            'password' => Hash::make('password'),
            'role_id' => 4, // Teacher role
            'is_active' => true,
        ]);

        // Create a test student
        User::create([
            'name' => 'Jane Student',
            'email' => 'student@edunexus.com',
            'password' => Hash::make('password'),
            'role_id' => 5, // Student role
            'is_active' => true,
        ]);
    }
}
