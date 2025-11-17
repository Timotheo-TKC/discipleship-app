<?php

namespace App\Services;

use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\Message;
use App\Models\MessageLog;
use App\Models\Mentorship;
use App\Notifications\CustomMessageNotification;
use Illuminate\Support\Facades\Notification;

class MessageService
{
    /**
     * Send a message to its recipients
     */
    public function sendMessage(Message $message): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $recipients = $message->payload['recipients'] ?? [];
        $subject = $message->payload['subject'] ?? 'Message from Discipleship System';
        $content = $message->template;

        // Get actual recipients based on message type
        $actualRecipients = $this->getRecipients($recipients, $message);

        foreach ($actualRecipients as $recipient) {
            try {
                if ($message->channel === 'email' && $recipient->user && $recipient->user->email) {
                    // Send via notification
                    $recipient->user->notify(
                        new CustomMessageNotification($subject, $content)
                    );

                    // Log success
                    MessageLog::create([
                        'message_id' => $message->id,
                        'recipient' => $recipient->user->email,
                        'channel' => $message->channel,
                        'result' => 'success',
                        'response' => ['sent' => true],
                        'created_at' => now(),
                    ]);

                    $results['success']++;
                } else {
                    // Log failure - no email or user
                    MessageLog::create([
                        'message_id' => $message->id,
                        'recipient' => $recipient->email ?? 'unknown',
                        'channel' => $message->channel,
                        'result' => 'failed',
                        'response' => ['error' => 'No email address or user account'],
                        'created_at' => now(),
                    ]);

                    $results['failed']++;
                    $results['errors'][] = "No email for {$recipient->full_name}";
                }
            } catch (\Exception $e) {
                // Log failure
                MessageLog::create([
                    'message_id' => $message->id,
                    'recipient' => $recipient->email ?? 'unknown',
                    'channel' => $message->channel,
                    'result' => 'failed',
                    'response' => ['error' => $e->getMessage()],
                    'created_at' => now(),
                ]);

                $results['failed']++;
                $results['errors'][] = "Error sending to {$recipient->full_name}: {$e->getMessage()}";
            }
        }

        // Update message status
        $message->update([
            'status' => $results['failed'] === 0 ? 'sent' : ($results['success'] > 0 ? 'sent' : 'failed'),
            'sent_at' => now(),
        ]);

        return $results;
    }

    /**
     * Get actual recipients based on message recipient types
     */
    protected function getRecipients(array $recipientTypes, Message $message): \Illuminate\Support\Collection
    {
        $recipients = collect();

        foreach ($recipientTypes as $type) {
            switch ($type) {
                case 'all_members':
                    $recipients = $recipients->merge(
                        Member::whereHas('user')->with('user')->get()
                    );
                    break;

                case 'class_members':
                    // Get members from all active classes
                    $classIds = DiscipleshipClass::where('is_active', true)->pluck('id');
                    $recipients = $recipients->merge(
                        Member::whereHas('enrollments', function ($query) use ($classIds) {
                            $query->whereIn('class_id', $classIds)
                                ->where('status', 'approved');
                        })
                        ->whereHas('user')
                        ->with('user')
                        ->get()
                    );
                    break;

                case 'mentorship_pairs':
                    // Get members in active mentorships
                    $memberIds = Mentorship::where('status', 'active')
                        ->pluck('member_id')
                        ->unique();
                    $recipients = $recipients->merge(
                        Member::whereIn('id', $memberIds)
                            ->whereHas('user')
                            ->with('user')
                            ->get()
                    );
                    break;
            }
        }

        // Remove duplicates
        return $recipients->unique('id');
    }
}

