<?php

namespace App\Listeners;

use App\Events\ClassCompleted;
use App\Notifications\ClassCompletionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCompletionMessage implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ClassCompleted $event): void
    {
        $enrollment = $event->enrollment;
        
        // Send completion message if enrollment is completed and member has user account
        if ($enrollment->status === 'completed' && $enrollment->member->user) {
            $enrollment->member->user->notify(
                new ClassCompletionNotification($enrollment)
            );
        }
    }
}
