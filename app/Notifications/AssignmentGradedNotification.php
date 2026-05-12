<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected AssignmentSubmission $submission;

    /**
     * Create a new notification instance.
     */
    public function __construct(AssignmentSubmission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $assignment = $this->submission->assignment;
        $grade = $this->submission->getGrade();
        $percentage = $this->submission->getPercentageScore();

        return (new MailMessage)
            ->subject('Assignment Graded: ' . $assignment->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your assignment has been graded.')
            ->line('**Grading Details:**')
            ->line('Assignment: ' . $assignment->title)
            ->line('Subject: ' . $assignment->subject->name)
            ->line('Marks Obtained: ' . $this->submission->marks_obtained . ' / ' . $assignment->total_marks)
            ->line('Grade: ' . $grade . ' (' . number_format($percentage, 1) . '%)')
            ->line('Graded Date: ' . $this->submission->graded_at->format('M j, Y g:i A'))
            
            ->when($this->submission->feedback, function ($message) {
                $message->line('**Feedback:**');
                $message->line($this->submission->feedback);
            })
            
            ->action('View Assignment', route('student.assignments.show', $assignment->id))
            ->line('Keep up the good work!')
            ->line('Thank you for using EduNexus!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $assignment = $this->submission->assignment;
        
        return [
            'assignment_id' => $assignment->id,
            'assignment_title' => $assignment->title,
            'subject' => $assignment->subject->name,
            'marks_obtained' => $this->submission->marks_obtained,
            'total_marks' => $assignment->total_marks,
            'percentage' => $this->submission->getPercentageScore(),
            'grade' => $this->submission->getGrade(),
            'feedback' => $this->submission->feedback,
            'graded_at' => $this->submission->graded_at->toISOString(),
            'graded_by' => $this->submission->gradedBy->name,
            'message' => 'Your assignment "' . $assignment->title . '" has been graded',
            'type' => 'assignment_graded'
        ];
    }
}
