<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Notifications\GeneralNotification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@edunexus.com')->first();
        
        if ($admin) {
            $notifications = [
                [
                    'message' => 'New student admission request pending approval',
                    'type' => 'student_admission',
                    'url' => '/admin/students'
                ],
                [
                    'message' => 'Fee payment deadline approaching for 15 students',
                    'type' => 'fee_reminder',
                    'url' => '/admin/fees/defaulters'
                ],
                [
                    'message' => 'Teacher meeting scheduled for tomorrow at 10:00 AM',
                    'type' => 'meeting',
                    'url' => '/admin/meetings'
                ],
                [
                    'message' => 'System backup completed successfully',
                    'type' => 'system',
                    'url' => null
                ],
                [
                    'message' => '3 new assignments submitted for grading',
                    'type' => 'assignment',
                    'url' => '/admin/assignments'
                ]
            ];
            
            foreach ($notifications as $notification) {
                $admin->notify(new GeneralNotification($notification));
            }
            
            $this->command->info('5 notifications created for admin user');
        }
    }
}
