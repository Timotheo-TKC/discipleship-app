<?php

namespace App\Console\Commands;

use App\Models\ClassSession;
use App\Models\Member;
use App\Notifications\ClassReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendClassRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'classes:send-reminders 
                            {--days=1 : Number of days before session to send reminder}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to enrolled members about upcoming class sessions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysBefore = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Looking for sessions scheduled for " . now()->addDays($daysBefore)->toDateString());

        // Find upcoming sessions scheduled for the target date
        $targetDate = now()->addDays($daysBefore)->toDateString();
        
        $sessions = ClassSession::whereDate('session_date', $targetDate)
            ->whereHas('class', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['class.enrolledMembers.member.user'])
            ->get();

        if ($sessions->isEmpty()) {
            $this->info("No upcoming sessions found for {$targetDate}.");
            return Command::SUCCESS;
        }

        $this->info("Found {$sessions->count()} session(s) scheduled for {$targetDate}");

        $totalSent = 0;
        $totalFailed = 0;

        foreach ($sessions as $session) {
            $class = $session->class;
            $enrollments = $class->enrollments()
                ->where('status', 'approved')
                ->with('member.user')
                ->get();

            $this->line("");
            $this->line("Processing: {$class->title} - {$session->topic}");
            $this->line("Date: {$session->session_date->format('Y-m-d')}");
            $this->line("Enrolled members: {$enrollments->count()}");

            if ($enrollments->isEmpty()) {
                $this->warn("  No enrolled members found for this session.");
                continue;
            }

            foreach ($enrollments as $enrollment) {
                $member = $enrollment->member;
                $user = $member->user;

                if (!$user || !$user->email) {
                    $this->warn("  Skipping {$member->full_name} - no email address");
                    $totalFailed++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [DRY RUN] Would send reminder to: {$user->email} ({$member->full_name})");
                    $totalSent++;
                } else {
                    try {
                        // Send notification to the User associated with the Member
                        $user->notify(new ClassReminderNotification($session, $daysBefore));
                        
                        $this->info("  ✓ Sent reminder to: {$user->email} ({$member->full_name})");
                        $totalSent++;
                        
                        Log::info("Class reminder sent", [
                            'session_id' => $session->id,
                            'member_id' => $member->id,
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'days_before' => $daysBefore,
                        ]);
                    } catch (\Exception $e) {
                        $this->error("  ✗ Failed to send to {$user->email}: {$e->getMessage()}");
                        $totalFailed++;
                        
                        Log::error("Class reminder failed", [
                            'session_id' => $session->id,
                            'member_id' => $member->id,
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        $this->line("");
        $this->info("=== Summary ===");
        $this->info("Total reminders: " . ($totalSent + $totalFailed));
        $this->info("Sent: {$totalSent}");
        if ($totalFailed > 0) {
            $this->warn("Failed: {$totalFailed}");
        }
        
        if ($dryRun) {
            $this->warn("DRY RUN - No emails were actually sent");
        }

        return Command::SUCCESS;
    }
}

