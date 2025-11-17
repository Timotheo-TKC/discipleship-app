<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomMessageNotification extends Notification
{

    public function __construct(
        public string $subject,
        public string $content
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
        // Convert content to HTML if it contains line breaks
        $htmlContent = nl2br(e($this->content));
        
        return (new MailMessage)
            ->subject($this->subject ?: 'Message from Discipleship System')
            ->greeting("Hello {$notifiable->name},")
            ->line($htmlContent)
            ->salutation("Blessings,\nYour Discipleship Team");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subject' => $this->subject,
            'content' => $this->content,
        ];
    }
}
