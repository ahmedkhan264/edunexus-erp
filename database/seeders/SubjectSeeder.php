<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH', 'description' => 'Mathematics and Algebra'],
            ['name' => 'Science', 'code' => 'SCI', 'description' => 'General Science'],
            ['name' => 'English', 'code' => 'ENG', 'description' => 'English Language and Literature'],
            ['name' => 'History', 'code' => 'HIST', 'description' => 'World History'],
            ['name' => 'Computer Science', 'code' => 'CS', 'description' => 'Computer Programming and IT'],
            ['name' => 'Physics', 'code' => 'PHY', 'description' => 'Physics and Physical Sciences'],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'description' => 'Chemistry'],
            ['name' => 'Biology', 'code' => 'BIO', 'description' => 'Biological Sciences'],
            ['name' => 'Geography', 'code' => 'GEO', 'description' => 'Geography and Earth Sciences'],
            ['name' => 'Economics', 'code' => 'ECO', 'description' => 'Economics and Business Studies']
        ];

        foreach ($subjects as $subject) {
            Subject::create(array_merge($subject, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        $this->command->info('SubjectSeeder completed successfully!');
        $this->command->info('Created ' . count($subjects) . ' subjects.');
    }
}
