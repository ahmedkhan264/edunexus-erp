<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\SchoolClass;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $students = Student::with('class')->get();
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $statuses = ['present', 'absent', 'late', 'holiday'];

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            foreach ($students as $student) {
                // Skip weekends (optional)
                if ($date->isWeekend()) {
                    continue;
                }

                // Random status, but 70% present, 15% absent, 10% late, 5% holiday
                $rand = rand(1, 100);
                if ($rand <= 70) $status = 'present';
                elseif ($rand <= 85) $status = 'absent';
                elseif ($rand <= 95) $status = 'late';
                else $status = 'holiday';

                Attendance::create([
                    'user_id' => $student->user_id,
                    'class_id' => $student->class_id,
                    'date' => $date->copy(),
                    'status' => $status,
                    'marked_by' => 1, // Admin user id
                ]);
            }
        }
    }
}
