<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MAT101'],
            ['name' => 'English', 'code' => 'ENG101'],
            ['name' => 'Physics', 'code' => 'PHY101'],
            ['name' => 'Chemistry', 'code' => 'CHE101'],
            ['name' => 'Biology', 'code' => 'BIO101'],
            ['name' => 'Computer Science', 'code' => 'CSC101'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
