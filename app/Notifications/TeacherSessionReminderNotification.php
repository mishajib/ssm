<?php

namespace App\Notifications;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class TeacherSessionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly Session $session)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line("Reminder: You have a session scheduled in 5 minutes for the following student: {$this->session->student->name}")
            ->line('Teacher: ' . $this->session->student->name)
            ->line('Start Time: ' . Carbon::parse($this->session->start_time)->toDateTimeString())
            ->line('End Time: ' . Carbon::parse($this->session->end_time)->toDateTimeString());
    }
}
