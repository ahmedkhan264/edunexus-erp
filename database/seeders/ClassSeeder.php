<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;

class ClassSeeder extends Seeder
{
    public function run()
    {
        $grades = range(1, 12);
        $sections = ['A', 'B', 'C'];

        foreach ($grades as $grade) {
            foreach ($sections as $section) {
                SchoolClass::create([
                    'grade_level' => $grade,
                    'section' => $section,
                    'name' => "Grade {$grade} - Section {$section}",
                    'class_code' => "G{$grade}-{$section}",
                ]);
            }
        }
    }
}
