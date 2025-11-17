<?php

namespace App\Notifications;

use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ClassSession $session,
        public int $daysBefore = 1
    ) {
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
        $class = $this->session->class;
        $sessionDate = $this->session->session_date->format('l, F d, Y');
        $sessionTime = $class->schedule_time ?? 'TBD';
        $daysText = $this->daysBefore === 1 ? 'tomorrow' : "in {$this->daysBefore} days";

        return (new MailMessage)
            ->subject("Reminder: {$class->title} Session {$daysText}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("This is a friendly reminder about your upcoming discipleship class session.")
            ->line("**Class:** {$class->title}")
            ->line("**Session Topic:** {$this->session->topic}")
            ->line("**Date:** {$sessionDate}")
            ->line("**Time:** {$sessionTime}")
            ->when($this->session->location, function ($mail) {
                return $mail->line("**Location:** {$this->session->location}");
            })
            ->when($this->session->google_meet_link, function ($mail) {
                return $mail->line("")
                    ->line("**Online Session:**")
                    ->action('Join Google Meet', $this->session->google_meet_link);
            })
            ->when($this->session->notes, function ($mail) {
                return $mail->line("")
                    ->line("**Additional Notes:**")
                    ->line($this->session->notes);
            })
            ->line("")
            ->line("We look forward to seeing you at the session!")
            ->salutation("Blessings,\n{$class->mentor->name ?? 'Your Discipleship Team'}");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'session_id' => $this->session->id,
            'session_topic' => $this->session->topic,
            'session_date' => $this->session->session_date->toDateString(),
            'class_title' => $this->session->class->title,
            'days_before' => $this->daysBefore,
        ];
    }
}

