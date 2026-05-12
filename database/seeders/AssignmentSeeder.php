<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get teacher user
        $teacher = User::whereHas('role', function($query) {
            $query->where('slug', 'teacher');
        })->first();

        if (!$teacher) {
            $this->command->error('No teacher user found. Please run UserSeeder first.');
            return;
        }

        // Get some classes and subjects
        $classes = SchoolClass::take(3)->get();
        $subjects = Subject::take(5)->get();

        if ($classes->isEmpty() || $subjects->isEmpty()) {
            $this->command->error('No classes or subjects found. Please run ClassSeeder and SubjectSeeder first.');
            return;
        }

        // Create sample assignments
        $assignments = [
            [
                'title' => 'Mathematics Assignment 1',
                'description' => 'Complete exercises 1-20 from Chapter 3: Algebra. Show all your work and steps.',
                'total_marks' => 100,
                'due_date' => now()->addDays(7),
                'allow_resubmission' => true,
                'section' => 'A'
            ],
            [
                'title' => 'Science Project',
                'description' => 'Create a presentation on renewable energy sources. Include diagrams and references.',
                'total_marks' => 150,
                'due_date' => now()->addDays(14),
                'allow_resubmission' => false,
                'section' => 'A'
            ],
            [
                'title' => 'English Essay',
                'description' => 'Write a 500-word essay on "The Impact of Technology on Education". Follow proper essay structure.',
                'total_marks' => 100,
                'due_date' => now()->addDays(5),
                'allow_resubmission' => true,
                'section' => 'B'
            ],
            [
                'title' => 'History Research Paper',
                'description' => 'Research and write about a historical event of your choice. Minimum 1000 words with citations.',
                'total_marks' => 200,
                'due_date' => now()->addDays(21),
                'allow_resubmission' => false,
                'section' => 'B'
            ],
            [
                'title' => 'Computer Science Programming',
                'description' => 'Write a Python program that calculates factorial using recursion. Include comments.',
                'total_marks' => 80,
                'due_date' => now()->addDays(10),
                'allow_resubmission' => true,
                'section' => 'C'
            ]
        ];

        foreach ($assignments as $index => $assignmentData) {
            $class = $classes[$index % $classes->count()];
            $subject = $subjects[$index % $subjects->count()];

            $assignment = Assignment::create(array_merge($assignmentData, [
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30))
            ]));

            // Create sample submissions for some assignments
            if ($index < 3) {
                $this->createSampleSubmissions($assignment);
            }
        }

        $this->command->info('AssignmentSeeder completed successfully!');
        $this->command->info('Created ' . count($assignments) . ' assignments with sample submissions.');
    }

    /**
     * Create sample submissions for an assignment
     */
    private function createSampleSubmissions(Assignment $assignment): void
    {
        // Get students from the assignment's class
        $students = Student::where('class_id', $assignment->class_id)
            ->where('section', $assignment->section)
            ->take(5)
            ->get();

        foreach ($students as $student) {
            // Randomly decide if student has submitted
            if (rand(1, 10) <= 7) { // 70% chance of submission
                $submission = AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'content' => $this->generateSampleContent($assignment->title),
                    'created_at' => $assignment->due_date->subDays(rand(1, 3)),
                    'updated_at' => now()->subDays(rand(1, 2))
                ]);

                // Randomly grade some submissions
                if (rand(1, 10) <= 5) { // 50% chance of being graded
                    $submission->update([
                        'marks_obtained' => rand(60, $assignment->total_marks),
                        'feedback' => $this->generateSampleFeedback($submission->marks_obtained, $assignment->total_marks),
                        'graded_at' => now()->subDays(1),
                        'graded_by' => $assignment->teacher_id
                    ]);
                }
            }
        }
    }

    /**
     * Generate sample content for assignment submission
     */
    private function generateSampleContent(string $assignmentTitle): string
    {
        $contents = [
            'This is my complete solution to the assignment. I have followed all the instructions carefully and included all required elements.',
            'I have completed this assignment to the best of my ability. Please review my work and provide feedback.',
            'Here is my submission for this assignment. I have put in significant effort and hope it meets your expectations.',
            'I have thoroughly researched this topic and presented my findings in this submission.',
            'This assignment was challenging but I learned a lot while working on it. I hope my work demonstrates my understanding.'
        ];

        return $contents[array_rand($contents)];
    }

    /**
     * Generate sample feedback
     */
    private function generateSampleFeedback(int $marksObtained, int $totalMarks): string
    {
        $percentage = ($marksObtained / $totalMarks) * 100;

        if ($percentage >= 90) {
            return 'Excellent work! Your submission demonstrates outstanding understanding of the concepts. Keep up the great effort!';
        } elseif ($percentage >= 80) {
            return 'Very good work! You have a strong grasp of the material. A few minor improvements could make this even better.';
        } elseif ($percentage >= 70) {
            return 'Good work overall. You understand most concepts well. Review the areas where you lost marks for improvement.';
        } elseif ($percentage >= 60) {
            return 'Satisfactory work. You have a basic understanding but need to review some concepts more thoroughly.';
        } else {
            return 'Your submission needs significant improvement. Please review the material and consider resubmitting if allowed.';
        }
    }
}
