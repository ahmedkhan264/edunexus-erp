<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Academic Affairs',
                'code' => 'ACAD',
                'description' => 'Responsible for curriculum development, academic standards, and educational quality assurance.',
                'head_of_department' => 'Dr. Sarah Johnson',
                'contact_email' => 'academic@edunexus.com',
                'contact_phone' => '+1-555-0101',
                'is_active' => true
            ],
            [
                'name' => 'Science & Technology',
                'code' => 'SCI',
                'description' => 'Department covering Physics, Chemistry, Biology, and Computer Science subjects.',
                'head_of_department' => 'Prof. Michael Chen',
                'contact_email' => 'science@edunexus.com',
                'contact_phone' => '+1-555-0102',
                'is_active' => true
            ],
            [
                'name' => 'Mathematics',
                'code' => 'MATH',
                'description' => 'Mathematics department covering all levels from basic arithmetic to advanced calculus.',
                'head_of_department' => 'Dr. Emily Rodriguez',
                'contact_email' => 'math@edunexus.com',
                'contact_phone' => '+1-555-0103',
                'is_active' => true
            ],
            [
                'name' => 'Languages & Literature',
                'code' => 'LANG',
                'description' => 'Department for English, Urdu, and other language studies.',
                'head_of_department' => 'Ms. Fatima Khan',
                'contact_email' => 'languages@edunexus.com',
                'contact_phone' => '+1-555-0104',
                'is_active' => true
            ],
            [
                'name' => 'Social Studies',
                'code' => 'SOC',
                'description' => 'History, Geography, Civics, and Social Sciences department.',
                'head_of_department' => 'Mr. James Wilson',
                'contact_email' => 'social@edunexus.com',
                'contact_phone' => '+1-555-0105',
                'is_active' => true
            ],
            [
                'name' => 'Physical Education',
                'code' => 'PE',
                'description' => 'Sports, physical fitness, and health education department.',
                'head_of_department' => 'Coach David Brown',
                'contact_email' => 'pe@edunexus.com',
                'contact_phone' => '+1-555-0106',
                'is_active' => true
            ],
            [
                'name' => 'Arts & Music',
                'code' => 'ART',
                'description' => 'Fine arts, music, drama, and creative studies department.',
                'head_of_department' => 'Ms. Lisa Anderson',
                'contact_email' => 'arts@edunexus.com',
                'contact_phone' => '+1-555-0107',
                'is_active' => true
            ],
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'description' => 'IT support, computer lab management, and digital learning resources.',
                'head_of_department' => 'Mr. Robert Taylor',
                'contact_email' => 'it@edunexus.com',
                'contact_phone' => '+1-555-0108',
                'is_active' => true
            ],
            [
                'name' => 'Student Services',
                'code' => 'STU',
                'description' => 'Student counseling, career guidance, and support services.',
                'head_of_department' => 'Ms. Jennifer Martinez',
                'contact_email' => 'students@edunexus.com',
                'contact_phone' => '+1-555-0109',
                'is_active' => true
            ],
            [
                'name' => 'Administrative Services',
                'code' => 'ADMIN',
                'description' => 'School administration, records, and operational support.',
                'head_of_department' => 'Mr. William Davis',
                'contact_email' => 'admin@edunexus.com',
                'contact_phone' => '+1-555-0110',
                'is_active' => true
            ]
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        $this->command->info('Departments seeded successfully!');
    }
}
