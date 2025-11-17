<?php

namespace App\Listeners;

use App\Events\ClassEnrollmentCreated;
use App\Notifications\WelcomeToClassNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeMessage implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ClassEnrollmentCreated $event): void
    {
        $enrollment = $event->enrollment;
        
        // Only send welcome message if enrollment is approved
        if ($enrollment->status === 'approved' && $enrollment->member->user) {
            $enrollment->member->user->notify(
                new WelcomeToClassNotification($enrollment)
            );
        }
    }
}
