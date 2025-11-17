<?php

namespace App\Notifications;

use App\Models\ClassEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeToClassNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ClassEnrollment $enrollment
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
        $class = $this->enrollment->class;
        $member = $this->enrollment->member;
        
        return (new MailMessage)
            ->subject("Welcome to {$class->title}!")
            ->greeting("Hello {$member->full_name},")
            ->line("We are thrilled to welcome you to **{$class->title}**!")
            ->line("Your enrollment has been confirmed and you now have full access to all class materials, sessions, and content.")
            ->when($class->description, function ($mail) use ($class) {
                return $mail->line("**About this class:** {$class->description}");
            })
            ->when($class->start_date, function ($mail) use ($class) {
                return $mail->line("**Start Date:** {$class->start_date->format('F d, Y')}");
            })
            ->when($class->schedule_time, function ($mail) use ($class) {
                $scheduleInfo = "**Schedule:** ";
                if ($class->schedule_day) {
                    $scheduleInfo .= "{$class->schedule_day}";
                }
                if ($class->schedule_time) {
                    $scheduleInfo .= " at {$class->schedule_time}";
                }
                return $mail->line($scheduleInfo);
            })
            ->when($class->location, function ($mail) use ($class) {
                return $mail->line("**Location:** {$class->location}");
            })
            ->when($class->duration_weeks, function ($mail) use ($class) {
                return $mail->line("**Duration:** {$class->duration_weeks} weeks");
            })
            ->line("")
            ->line("We encourage you to:")
            ->line("• Review the class outline and materials")
            ->line("• Attend all scheduled sessions")
            ->line("• Complete assignments and participate actively")
            ->line("• Reach out to your mentor if you have any questions")
            ->action('View Class Details', url("/classes/{$class->id}"))
            ->line("")
            ->line("We're excited to journey with you through this discipleship class!")
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
            'enrollment_id' => $this->enrollment->id,
            'class_id' => $this->enrollment->class_id,
            'class_title' => $this->enrollment->class->title,
            'member_name' => $this->enrollment->member->full_name,
        ];
    }
}
