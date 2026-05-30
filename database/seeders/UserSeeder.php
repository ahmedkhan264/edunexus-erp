<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['name' => 'Admin User', 'email' => 'admin@edunexus.com', 'password' => 'password', 'role' => 'Admin'],
            ['name' => 'Teacher User', 'email' => 'teacher@edunexus.com', 'password' => 'password', 'role' => 'Teacher'],
            ['name' => 'Student User', 'email' => 'student@edunexus.com', 'password' => 'password', 'role' => 'Student'],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            
            if ($role) {
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'role_id' => $role->id,
                ]);
            }
        }
    }
}
