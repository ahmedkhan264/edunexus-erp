<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [];
        $sections = ['A', 'B', 'C'];
        
        // Create classes for Grade 1-12
        for ($grade = 1; $grade <= 12; $grade++) {
            foreach ($sections as $section) {
                $classes[] = [
                    'name' => "Grade {$grade}",
                    'class_code' => "G{$grade}-{$section}",
                    'section' => $section,
                    'grade_level' => $grade,
                    'capacity' => 30,
                    'description' => "Grade {$grade} Section {$section}",
                    'is_active' => true,
                    'teacher_id' => null, // Will be assigned later
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Insert all classes
        SchoolClass::insert($classes);
        
        $this->command->info('Created ' . count($classes) . ' classes (Grade 1-12 with sections A, B, C)');
    }
}
