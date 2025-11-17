<?php

namespace App\Notifications;

use App\Models\EmailVerificationOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public EmailVerificationOtp $otpRecord;

    /**
     * Create a new notification instance.
     */
    public function __construct(EmailVerificationOtp $otpRecord)
    {
        $this->otpRecord = $otpRecord;
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
        $otp = $this->otpRecord->otp;
        $expiresAt = $this->otpRecord->expires_at->format('g:i A');

        return (new MailMessage)
            ->subject('Verify Your Email Address - OTP Code')
            ->greeting("Hello {$notifiable->name},")
            ->line('Thank you for registering! Please use the following OTP code to verify your email address.')
            ->line('**Your verification code is:**')
            ->line("## **{$otp}**")
            ->line("This code will expire in **10 minutes** (at {$expiresAt}).")
            ->line('If you did not request this code, please ignore this email.')
            ->salutation('Thank you for joining us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'otp' => $this->otpRecord->otp,
            'expires_at' => $this->otpRecord->expires_at->toIso8601String(),
        ];
    }
}
