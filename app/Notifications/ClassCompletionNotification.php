<?php

namespace App\Notifications;

use App\Models\ClassEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassCompletionNotification extends Notification implements ShouldQueue
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
            ->subject("Congratulations on Completing {$class->title}!")
            ->greeting("Hello {$member->full_name},")
            ->line("**Congratulations!** 🎉")
            ->line("You have successfully completed **{$class->title}**!")
            ->line("We are proud of your commitment, dedication, and active participation throughout this discipleship journey.")
            ->line("")
            ->line("**Your Achievement:**")
            ->line("✅ Completed all class sessions")
            ->line("✅ Engaged with class materials")
            ->line("✅ Finished your discipleship course")
            ->line("")
            ->line("This is a significant milestone in your spiritual growth journey. We encourage you to:")
            ->line("• Continue applying what you've learned")
            ->line("• Stay connected with your mentor and classmates")
            ->line("• Consider enrolling in the next level class")
            ->line("• Share your testimony with others")
            ->when($class->mentor, function ($mail) use ($class) {
                return $mail->line("")
                    ->line("Your mentor **{$class->mentor->name}** is available for continued support and guidance.");
            })
            ->action('Browse More Classes', url("/classes"))
            ->line("")
            ->line("Well done, faithful servant! We look forward to continuing this journey with you.")
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
            'completed_at' => $this->enrollment->updated_at->toIso8601String(),
        ];
    }
}
