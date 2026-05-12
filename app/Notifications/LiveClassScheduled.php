<?php

namespace App\Notifications;

use App\Models\LiveClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LiveClassScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    protected LiveClass $liveClass;

    /**
     * Create a new notification instance.
     */
    public function __construct(LiveClass $liveClass)
    {
        $this->liveClass = $liveClass;
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
            ->subject('New Live Class Scheduled: ' . $this->liveClass->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new live class has been scheduled for your class.')
            ->line('**Class Details:**')
            ->line('Title: ' . $this->liveClass->title)
            ->line('Subject: ' . $this->liveClass->subject->name)
            ->line('Class: Grade ' . $this->liveClass->schoolClass->grade_level . ' - ' . $this->liveClass->section)
            ->line('Teacher: ' . $this->liveClass->teacher->name)
            ->line('Date: ' . $this->liveClass->start_time->format('l, F j, Y'))
            ->line('Time: ' . $this->liveClass->start_time->format('g:i A') . ' - ' . $this->liveClass->end_time->format('g:i A'))
            ->line('Duration: ' . $this->liveClass->getFormattedDuration())
            ->line('Platform: ' . $this->liveClass->getMeetingPlatform())
            
            ->when($this->liveClass->description, function ($message) {
                $message->line('Description: ' . $this->liveClass->description);
            })
            
            ->action('Join Live Class', $this->liveClass->meeting_link)
            ->line('Please join the class on time using the meeting link above.')
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
            'live_class_id' => $this->liveClass->id,
            'title' => $this->liveClass->title,
            'subject' => $this->liveClass->subject->name,
            'class' => 'Grade ' . $this->liveClass->schoolClass->grade_level . ' - ' . $this->liveClass->section,
            'teacher' => $this->liveClass->teacher->name,
            'start_time' => $this->liveClass->start_time->toISOString(),
            'end_time' => $this->liveClass->end_time->toISOString(),
            'duration' => $this->liveClass->duration,
            'meeting_link' => $this->liveClass->meeting_link,
            'meeting_platform' => $this->liveClass->getMeetingPlatform(),
            'description' => $this->liveClass->description,
            'message' => 'New live class scheduled: ' . $this->liveClass->title,
            'type' => 'live_class_scheduled'
        ];
    }
}
