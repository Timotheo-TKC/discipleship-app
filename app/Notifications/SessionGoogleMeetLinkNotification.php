<?php

namespace App\Notifications;

use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionGoogleMeetLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ClassSession $session
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
        $sessionTime = $this->session->session_date->format('g:i A');

        return (new MailMessage)
            ->subject("Google Meet Link for {$class->title} - {$this->session->topic}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("You have been invited to join an online session for **{$class->title}**.")
            ->line("**Session Details:**")
            ->line("- **Topic:** {$this->session->topic}")
            ->line("- **Date:** {$sessionDate}")
            ->line("- **Time:** {$sessionTime}")
            ->when($this->session->location, function ($mail) {
                return $mail->line("- **Location:** {$this->session->location}");
            })
            ->line("")
            ->line("**Join the session using the Google Meet link below:**")
            ->action('Join Google Meet', $this->session->google_meet_link)
            ->line("Please join a few minutes before the session starts.")
            ->when($this->session->notes, function ($mail) {
                return $mail->line("")
                    ->line("**Additional Notes:**")
                    ->line($this->session->notes);
            })
            ->line("")
            ->line("We look forward to seeing you at the session!")
            ->salutation("Blessings,\n{$class->mentor->name}");
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
            'google_meet_link' => $this->session->google_meet_link,
        ];
    }
}
