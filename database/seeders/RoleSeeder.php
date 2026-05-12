<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'Full system access, multi-institute management',
            ],
            [
                'name' => 'Principal',
                'slug' => 'principal',
                'description' => 'Executive dashboard, approvals, reports',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Day-to-day operations, student/staff management',
            ],
            [
                'name' => 'Teacher',
                'slug' => 'teacher',
                'description' => 'Attendance, LMS content, assignments, live classes',
            ],
            [
                'name' => 'Student',
                'slug' => 'student',
                'description' => 'Lectures, assignments, fees, timetable, results',
            ],
            [
                'name' => 'Parent',
                'slug' => 'parent',
                'description' => 'Child\'s attendance, fees, performance monitoring',
            ],
            [
                'name' => 'Accountant',
                'slug' => 'accountant',
                'description' => 'Fee challans, payments, finance reports',
            ],
            [
                'name' => 'HR Manager',
                'slug' => 'hr_manager',
                'description' => 'Staff records, leave, payroll, salary slips',
            ],
            [
                'name' => 'Librarian',
                'slug' => 'librarian',
                'description' => 'Books, issue/return, overdue tracking',
            ],
            [
                'name' => 'Timetable Coordinator',
                'slug' => 'timetable_coordinator',
                'description' => 'Class scheduling, teacher/room assignment',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
