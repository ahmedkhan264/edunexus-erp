<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Assignment $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
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
        return (new MailMessage)
            ->subject('New Assignment: ' . $this->assignment->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new assignment has been posted for your class.')
            ->line('**Assignment Details:**')
            ->line('Title: ' . $this->assignment->title)
            ->line('Subject: ' . $this->assignment->subject->name)
            ->line('Class: Grade ' . $this->assignment->schoolClass->grade_level . ' - ' . $this->assignment->section)
            ->line('Teacher: ' . $this->assignment->teacher->name)
            ->line('Due Date: ' . $this->assignment->getFormattedDueDate())
            ->line('Total Marks: ' . $this->assignment->total_marks)
            
            ->when($this->assignment->description, function ($message) {
                $message->line('Description: ' . $this->assignment->description);
            })
            
            ->when($this->assignment->files->count() > 0, function ($message) {
                $message->line('Files: ' . $this->assignment->files->count() . ' file(s) attached');
            })
            
            ->action('View Assignment', route('student.assignments.show', $this->assignment->id))
            ->line('Please submit your assignment before the due date.')
            ->line('Thank you for using EduNexus!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'subject' => $this->assignment->subject->name,
            'class' => 'Grade ' . $this->assignment->schoolClass->grade_level . ' - ' . $this->assignment->section,
            'teacher' => $this->assignment->teacher->name,
            'due_date' => $this->assignment->due_date->toISOString(),
            'total_marks' => $this->assignment->total_marks,
            'description' => $this->assignment->description,
            'files_count' => $this->assignment->files->count(),
            'allow_resubmission' => $this->assignment->allow_resubmission,
            'message' => 'New assignment posted: ' . $this->assignment->title,
            'type' => 'new_assignment'
        ];
    }
}
