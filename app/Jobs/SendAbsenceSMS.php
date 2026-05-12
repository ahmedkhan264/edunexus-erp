<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAbsenceSMS implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $absenceData
    ) {
        $this->onQueue('sms');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $studentName = $this->absenceData['student_name'];
            $parentPhone = $this->absenceData['parent_phone'];
            $className = $this->absenceData['class_name'];
            $date = $this->absenceData['date'];

            // In a real implementation, you would integrate with an SMS service
            // For now, we'll just log the SMS that would be sent
            $message = "Dear Parent, your child {$studentName} was absent today ({$date}) from class {$className}. Please contact the school for more information.";
            
            Log::info("SMS would be sent to {$parentPhone}: {$message}");

            // Example SMS integration (commented out):
            // $smsService = app(SmsService::class);
            // $smsService->send($parentPhone, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send absence SMS: ' . $e->getMessage(), [
                'absence_data' => $this->absenceData,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }
}
