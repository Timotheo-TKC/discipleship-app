<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Console\Command;

class SendScheduledMessagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:send-scheduled {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all scheduled messages that are due to be sent';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $service = new MessageService();

        // Find messages that are due to be sent
        $messages = Message::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        $this->info("Found {$messages->count()} scheduled message(s) due to be sent.");

        if ($messages->isEmpty()) {
            $this->info('No scheduled messages to send.');
            return Command::SUCCESS;
        }

        $totalSent = 0;
        $totalFailed = 0;

        foreach ($messages as $message) {
            $this->line("");
            $subject = $message->payload['subject'] ?? 'No Subject';
            $this->line("Processing message ID {$message->id}: {$subject}");

            if ($isDryRun) {
                $this->warn("  [DRY RUN] Would send this message now");
                $totalSent++;
            } else {
                try {
                    $results = $service->sendMessage($message);
                    
                    if ($results['success'] > 0) {
                        $this->info("  ✓ Sent to {$results['success']} recipient(s)");
                        $totalSent++;
                    }
                    
                    if ($results['failed'] > 0) {
                        $this->error("  ✗ Failed to send to {$results['failed']} recipient(s)");
                        $totalFailed++;
                        
                        foreach ($results['errors'] as $error) {
                            $this->line("    - {$error}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("  ✗ Error: {$e->getMessage()}");
                    $totalFailed++;
                }
            }
        }

        $this->line("");
        $this->info("=== Summary ===");
        $this->info("Processed: {$messages->count()}");
        $this->info("Sent: {$totalSent}");
        
        if ($totalFailed > 0) {
            $this->error("Failed: {$totalFailed}");
        }

        if ($isDryRun) {
            $this->warn("DRY RUN - No messages were actually sent");
        }

        return Command::SUCCESS;
    }
}
