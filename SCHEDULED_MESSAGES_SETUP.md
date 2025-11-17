# Scheduled Messages Setup

## Overview

The automated message sending system is now configured to automatically send scheduled messages at their designated times.

## How It Works

1. **Scheduled Command**: `messages:send-scheduled`
   - Checks for messages with `status = 'scheduled'` and `scheduled_at <= now()`
   - Sends each due message to its recipients
   - Updates message status to `sent` or `failed`

2. **Automated Execution**: 
   - Runs every minute via Laravel's task scheduler
   - Configurable in `routes/console.php`

## Setup Instructions

### For Local Development

The scheduler runs automatically when you use:
```bash
php artisan schedule:work
```

Or add it to your development setup:
```bash
# In a separate terminal
php artisan schedule:work
```

### For Production

Add this cron entry to your server (runs every minute):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or ensure `schedule:work` is running as a daemon/service.

### Docker Setup

If using Docker, the supervisor configuration already includes:
```ini
[program:laravel-schedule]
command=php /var/www/html/artisan schedule:work
autostart=true
autorestart=true
```

This will automatically start when the container starts.

## Configuration

### Change Frequency

Edit `routes/console.php` to change how often scheduled messages are checked:

```php
// Check every 5 minutes instead of every minute
Schedule::command('messages:send-scheduled')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Or check at specific times
Schedule::command('messages:send-scheduled')
    ->hourly()
    ->at('15'); // At 15 minutes past every hour

// Or daily at specific time
Schedule::command('messages:send-scheduled')
    ->dailyAt('09:00');
```

### Logging

Logs are automatically written to: `storage/logs/scheduled-messages.log`

To view logs:
```bash
tail -f storage/logs/scheduled-messages.log
```

### Manual Testing

Test the scheduled command manually:
```bash
# Dry run (shows what would be sent without actually sending)
php artisan messages:send-scheduled --dry-run

# Actually send scheduled messages
php artisan messages:send-scheduled
```

## Message Scheduling

When creating a message:

1. **Immediate**: Set `schedule_type = 'immediate'`
   - Message is created with `status = 'draft'`
   - Click "Send Now" to send immediately

2. **Scheduled**: Set `schedule_type = 'scheduled'` and provide `scheduled_at` datetime
   - Message is created with `status = 'scheduled'`
   - Automatically sent when `scheduled_at` time is reached

## Automated Messages

The following messages are sent automatically:

1. **Welcome Message**: Sent when a member enrolls in a class
   - Triggered by `ClassEnrollmentCreated` event
   - Sent via `WelcomeToClassNotification`

2. **Completion Message**: Sent when a member completes a class
   - Triggered by `ClassCompleted` event (when enrollment status changes to 'completed')
   - Sent via `ClassCompletionNotification`

## Troubleshooting

### Messages Not Sending

1. Check if scheduler is running:
   ```bash
   php artisan schedule:list
   ```

2. Check logs:
   ```bash
   tail -f storage/logs/scheduled-messages.log
   tail -f storage/logs/laravel.log
   ```

3. Verify queue worker is running (for queued notifications):
   ```bash
   php artisan queue:work
   ```

4. Check message status in database:
   ```sql
   SELECT * FROM messages WHERE status = 'scheduled' AND scheduled_at <= NOW();
   ```

### Queue Issues

If notifications are queued, ensure queue worker is running:
```bash
php artisan queue:work --tries=3
```

Or use supervisor/systemd to keep it running.

## Monitoring

### Check Scheduled Messages

View messages that are scheduled:
```bash
php artisan tinker
>>> \App\Models\Message::where('status', 'scheduled')->where('scheduled_at', '<=', now())->count();
```

### View Schedule

List all scheduled tasks:
```bash
php artisan schedule:list
```

## Best Practices

1. **Frequency**: Checking every minute ensures timely delivery, but you can reduce frequency if needed
2. **Overlapping**: `withoutOverlapping()` prevents multiple instances from running simultaneously
3. **Background**: `runInBackground()` prevents blocking other scheduled tasks
4. **Logging**: Monitor logs regularly to catch any issues

