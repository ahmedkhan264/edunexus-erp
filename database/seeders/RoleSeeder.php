<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'Super Admin', 'Principal', 'Admin', 'Teacher', 'Student',
            'Parent', 'Accountant', 'HR Manager', 'Librarian', 'Timetable Coordinator'
        ];

        foreach ($roles as $role) {
            Role::create([
                'name' => $role,
                'slug' => \Illuminate\Support\Str::slug($role)
            ]);
        }
    }
}
