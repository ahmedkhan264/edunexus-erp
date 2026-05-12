<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get test users and classes
        $teacher = User::where('email', 'teacher@edunexus.com')->first();
        $student = User::where('email', 'student@edunexus.com')->first();
        $admin = User::where('email', 'admin@edunexus.com')->first();
        
        $classes = SchoolClass::take(3)->get(); // Get first 3 classes
        
        $attendanceRecords = [];
        $statuses = ['present', 'absent', 'late'];
        $methods = ['manual', 'barcode', 'api'];
        
        // Generate attendance data for the last 30 days
        for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo)->toDateString();
            
            // Skip weekends (Saturday, Sunday)
            if (now()->subDays($daysAgo)->isWeekend()) {
                continue;
            }
            
            foreach ($classes as $class) {
                // Create attendance for student
                if ($student) {
                    $attendanceRecords[] = [
                        'user_id' => $student->id,
                        'class_id' => $class->id,
                        'date' => $date,
                        'status' => $statuses[array_rand($statuses)],
                        'check_in_time' => now()->subDays($daysAgo)->setHour(8 + rand(0, 1))->setMinute(rand(0, 59)),
                        'check_out_time' => now()->subDays($daysAgo)->setHour(14 + rand(0, 1))->setMinute(rand(0, 59)),
                        'marked_by' => $admin->id,
                        'remarks' => rand(0, 10) > 8 ? 'Remarks ' . rand(1, 100) : null,
                        'attendance_method' => $methods[array_rand($methods)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                // Create attendance for teacher
                if ($teacher) {
                    $attendanceRecords[] = [
                        'user_id' => $teacher->id,
                        'class_id' => $class->id,
                        'date' => $date,
                        'status' => 'present', // Teachers are usually present
                        'check_in_time' => now()->subDays($daysAgo)->setHour(7 + rand(0, 1))->setMinute(rand(0, 59)),
                        'check_out_time' => now()->subDays($daysAgo)->setHour(15 + rand(0, 1))->setMinute(rand(0, 59)),
                        'marked_by' => $admin->id,
                        'remarks' => null,
                        'attendance_method' => 'manual',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        // Insert all attendance records individually to avoid issues
        if (!empty($attendanceRecords)) {
            foreach ($attendanceRecords as $record) {
                try {
                    Attendance::create($record);
                } catch (\Exception $e) {
                    // Skip duplicate records
                    continue;
                }
            }
        }
        
        // Create some holiday records
        $holidays = [
            ['2026-04-25', 'Eid Holiday'],
            ['2026-04-26', 'Eid Holiday'],
            ['2026-05-01', 'Labour Day'],
        ];
        
        foreach ($holidays as [$date, $remark]) {
            foreach ($classes as $class) {
                if ($student) {
                    Attendance::create([
                        'user_id' => $student->id,
                        'class_id' => $class->id,
                        'date' => $date,
                        'status' => 'holiday',
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'marked_by' => $admin->id,
                        'remarks' => $remark,
                        'attendance_method' => 'manual',
                    ]);
                }
                
                if ($teacher) {
                    Attendance::create([
                        'user_id' => $teacher->id,
                        'class_id' => $class->id,
                        'date' => $date,
                        'status' => 'holiday',
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'marked_by' => $admin->id,
                        'remarks' => $remark,
                        'attendance_method' => 'manual',
                    ]);
                }
            }
        }
        
        $this->command->info('Created ' . count($attendanceRecords) . ' attendance records for the last 30 days');
        $this->command->info('Added ' . (count($holidays) * $classes->count() * 2) . ' holiday records');
    }
}
